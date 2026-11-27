<?php

namespace App\Http\Controllers;

use App\Exports\ServiceOrdersExport;
use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Services\TechnicalSheetWorkflow;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ServiceOrderController extends Controller
{
    const STATUS_CREATED = 'created';

    const STATUS_UNDER_ANALYSIS = 'under_analysis';

    const STATUS_APPROVED = 'approved';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $allowed = ['order_number', 'client_name', 'origin', 'order_date', 'status', 'created_at'];
        $sort = in_array($request->sort, $allowed) ? $request->sort : 'created_at';
        $dir = $request->direction === 'asc' ? 'asc' : 'desc';
        $perPage = min(max($request->integer('per_page', 10), 1), 100);

        $serviceOrders = ServiceOrder::query()
            ->withCount('items')
            ->when($request->order_number, fn ($q, $v) => $q->where('order_number', 'like', "%{$v}%"))
            ->when($request->client_name, fn ($q, $v) => $q->where('client_name', 'like', "%{$v}%"))
            ->when($request->consultant_name, fn ($q, $v) => $q->where('consultant_name', 'like', "%{$v}%"))
            ->when($request->origin, fn ($q, $v) => $q->where('origin', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('order_date', '>=', $v))
            ->when($request->date_to, fn ($q, $v) => $q->whereDate('order_date', '<=', $v))
            ->orderBy($sort, $dir)
            ->paginate($perPage);

        return view('service-orders.index', compact('serviceOrders'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $categoriasPrincipais = $this->listarCategoriasPrincipais();

        return view('service-orders.create', compact('products', 'categoriasPrincipais'));
    }

    public function store(Request $request)
    {
        if (empty($request->client_code)) {
            $request->merge(['client_code' => '0']);
        }

        try {
            $validated = $request->validate([
                'client_name' => 'required|string|max:255',
                'client_document' => 'nullable|string|max:20',
                'client_phone' => 'nullable|string|max:20',
                'consultant_name' => 'required|string|max:255',
                'client_code' => 'required|string|max:50',
                'origin' => 'required|in:SHOWROOM SP,SHOWROOM LIMEIRA,SHOWROOM ON-LINE,E-COMMERCE,EVENTOS',
                'order_date' => 'required|date',
                'observations' => 'nullable|string',
                'items' => 'nullable|array',
                'items.*.purchase_date' => 'nullable|date',
                'items.*.piece_value' => 'nullable|numeric|min:0|max:99999999.99',
            ]);

            $serviceOrderData = collect($validated)->except('items')->toArray();
            $serviceOrderData['status'] = self::STATUS_CREATED;

            $serviceOrder = ServiceOrder::create($serviceOrderData);

            if (! empty($request->items) && is_array($request->items)) {
                foreach ($request->items as $itemData) {
                    $item = new ServiceOrderItem([
                        'identified_product_name' => $itemData['product_name'] ?? null,
                        'services' => $itemData['services'] ?? [],
                        'coating' => $itemData['coating'] ?? null,
                        'observations' => $itemData['observations'] ?? null,
                        'purchase_date' => $itemData['purchase_date'] ?? null,
                        'piece_value' => $itemData['piece_value'] ?? null,
                        'ai_identified_code' => $itemData['ai_identified_code'] ?? null,
                        'is_guarantee' => null,
                        'repair_price' => null,
                        'technical_status' => null,
                    ]);

                    if (isset($itemData['product_image']) && $itemData['product_image']->isValid()) {
                        $file = $itemData['product_image'];
                        $imagePath = $file->store('service-order-items', 'public');
                        $item->product_image = $imagePath;
                    }

                    $serviceOrder->items()->save($item);
                }
            }

            return redirect()
                ->route('service-orders.show', $serviceOrder)
                ->with('success', 'Ordem de Serviço criada com sucesso! Aguardando análise técnica.');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Não foi possível salvar a ordem de serviço. Verifique os dados e tente novamente.']);
        }
    }

    public function identifyProduct(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'product_type' => 'required|string',
            ]);

            $tempPath = $request->file('image')->getPathname();
            $url = rtrim((string) config('services.flask.url'), '/').'/analisar';

            $response = Http::timeout(60)
                ->connectTimeout(5)
                ->attach('file', file_get_contents($tempPath), 'image.jpg')
                ->post($url, [
                    'produto' => $request->product_type,
                ]);

            if ($response->failed()) {
                Log::warning('Falha na análise de IA', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'O serviço de análise não conseguiu processar a imagem.',
                ], 502);
            }

            $data = $response->json();
            $bestMatch = $data['mais_semelhantes'][0] ?? null;
            $identifiedCode = $bestMatch ? $this->extractCodeFromFileName($bestMatch['arquivo']) : null;

            $matchedProduct = null;
            if ($identifiedCode) {
                $matchedProduct = Product::where('code', 'like', "%{$identifiedCode}%")
                    ->where('is_active', true)
                    ->first();
            }

            return response()->json([
                'success' => true,
                'ai_results' => $data,
                'identified_code' => $identifiedCode,
                'matched_product' => $matchedProduct ? [
                    'id' => $matchedProduct->id,
                    'code' => $matchedProduct->code,
                    'name' => $matchedProduct->name,
                ] : null,
                'similarity' => $bestMatch ? round($bestMatch['similaridade'] * 100, 1) : 0,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'error' => 'Não foi possível concluir a análise da imagem. Tente novamente.',
            ], 500);
        }
    }

    private function listarCategoriasPrincipais()
    {
        return [
            'Aneis',
            'Brincos',
            'Broches',
            'Colares',
            'Conjuntos',
            'Pingentes',
            'Pulseiras',
            'Tornozeleiras',
        ];
    }

    private function extractCodeFromFileName($fileName)
    {
        $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        $cleaned = preg_replace('/[^A-Z0-9]/', '', strtoupper($fileNameWithoutExt));

        if (preg_match('/[A-Z]{2}\d{8}[A-Z]{2}/', $cleaned, $matches)) {
            return $matches[0];
        }

        if (preg_match('/[A-Z]{2}\d+[A-Z]*/', $cleaned, $matches)) {
            return $matches[0];
        }

        return null;
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load('items.product', 'items.technicalSheet', 'technicalAnalyst');

        return view('service-orders.show', compact('serviceOrder'));
    }

    public function edit(ServiceOrder $serviceOrder)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('service-orders.show', $serviceOrder)
                ->with('warning', 'Apenas administradores podem editar ordens de serviço.');
        }

        $serviceOrder->load('items.product');

        $products = Product::where('is_active', true)->get();

        $categoriasPrincipais = $this->listarCategoriasPrincipais();

        return view('service-orders.edit', compact('serviceOrder', 'products', 'categoriasPrincipais'));
    }

    public function update(Request $request, ServiceOrder $serviceOrder)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.purchase_date' => 'nullable|date',
            'items.*.piece_value' => 'nullable|numeric|min:0|max:99999999.99',
        ]);
        $submittedIds = collect($request->input('items', []))->pluck('id')->filter()->all();
        if ($serviceOrder->items()->whereHas('technicalSheet')->whereNotIn('id', $submittedIds)->exists()) {
            return back()->withInput()->with('error', 'Peças com ficha técnica não podem ser removidas da OS.');
        }
        try {
            $serviceOrder->update([
                'client_name' => $request->client_name,
                'client_document' => $request->client_document,
                'client_phone' => $request->client_phone,
                'consultant_name' => $request->consultant_name,
                'client_code' => $request->client_code,
                'order_date' => $request->order_date,
                'origin' => $request->origin,
                'observations' => $request->observations,
            ]);

            $existingItemIds = $serviceOrder->items->pluck('id')->toArray();
            $updatedItemIds = [];

            foreach ($request->items as $itemData) {

                $itemData['services'] = $itemData['services'] ?? [];

                $productName = $itemData['product_name']
                    ?? $itemData['identified_product_name']
                    ?? null;

                $item = null;

                if (! empty($itemData['id'])) {

                    $item = $serviceOrder->items()->find($itemData['id']);

                    if ($item) {

                        $item->update([
                            'identified_product_name' => $productName,
                            'product_id' => null,
                            'coating' => $itemData['coating'] ?? null,
                            'services' => $itemData['services'],
                            'observations' => $itemData['observations'] ?? null,
                            'purchase_date' => $itemData['purchase_date'] ?? null,
                            'piece_value' => $itemData['piece_value'] ?? null,
                            'ai_identified_code' => $itemData['ai_identified_code'] ?? null,
                        ]);

                        $updatedItemIds[] = $item->id;
                    } else {

                        $item = $serviceOrder->items()->create([
                            'identified_product_name' => $productName,
                            'product_id' => null,
                            'coating' => $itemData['coating'] ?? null,
                            'services' => $itemData['services'],
                            'observations' => $itemData['observations'] ?? null,
                            'purchase_date' => $itemData['purchase_date'] ?? null,
                            'piece_value' => $itemData['piece_value'] ?? null,
                            'ai_identified_code' => $itemData['ai_identified_code'] ?? null,
                        ]);

                        $updatedItemIds[] = $item->id;
                    }
                } else {

                    $item = $serviceOrder->items()->create([
                        'identified_product_name' => $productName,
                        'product_id' => null,
                        'coating' => $itemData['coating'] ?? null,
                        'services' => $itemData['services'],
                        'observations' => $itemData['observations'] ?? null,
                        'purchase_date' => $itemData['purchase_date'] ?? null,
                        'piece_value' => $itemData['piece_value'] ?? null,
                        'ai_identified_code' => $itemData['ai_identified_code'] ?? null,
                    ]);

                    $updatedItemIds[] = $item->id;
                }

                // upload da imagem
                if (
                    isset($itemData['product_image']) &&
                    $itemData['product_image'] instanceof \Illuminate\Http\UploadedFile
                ) {
                    $path = $itemData['product_image']
                        ->store('service-order-items', 'public');

                    $item->update([
                        'product_image' => $path,
                    ]);
                }
            }

            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (! empty($itemsToDelete)) {
                $serviceOrder->items()->whereIn('id', $itemsToDelete)->delete();
            }

            // A newly added piece reopens an OS that already has technical work.
            if (
                $serviceOrder->status !== 'cancelled' && $serviceOrder->items()->whereHas('technicalSheet')->exists()
                && $serviceOrder->items()->where(function ($query) {
                    $query->whereDoesntHave('technicalSheet')->orWhereHas('technicalSheet', function ($sheet) {
                        $sheet->whereNotIn('status', \App\Models\TechnicalSheet::CLOSED_STATUSES);
                    });
                })->exists()
            ) {
                $serviceOrder->update(['status' => ServiceOrder::STATUS_UNDER_ANALYSIS]);
            }

            return redirect()->route('service-orders.show', $serviceOrder)
                ->with('success', 'Ordem de Serviço atualizada com sucesso!');
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Não foi possível atualizar a ordem de serviço. Verifique os dados e tente novamente.');
        }
    }

    public function destroy(ServiceOrder $serviceOrder)
    {
        $this->checkAdmin();

        $imagePaths = DB::transaction(function () use ($serviceOrder) {
            $order = ServiceOrder::query()->lockForUpdate()->findOrFail($serviceOrder->getKey());

            if ($order->items()->whereHas('technicalSheet')->exists()) {
                return null;
            }

            $imagePaths = $order->items()->lockForUpdate()->pluck('product_image')
                ->filter(fn ($path) => is_string($path) && preg_match('~\Aservice-order-items/[a-zA-Z0-9_-][a-zA-Z0-9_.-]*\z~', $path))
                ->unique();

            $order->delete();

            return $imagePaths;
        });

        if ($imagePaths === null) {
            return back()->with('error', 'Esta OS possui histórico técnico e não pode ser excluída.');
        }

        $redirect = redirect()->route('service-orders.index');

        if ($imagePaths->isNotEmpty()) {
            $sharedPaths = ServiceOrderItem::whereIn('product_image', $imagePaths)->pluck('product_image');
            $unusedPaths = $imagePaths->diff($sharedPaths);

            if ($unusedPaths->isNotEmpty() && ! Storage::disk('public')->delete($unusedPaths->all())) {
                Log::warning('Não foi possível remover todas as fotos da OS excluída.', [
                    'service_order_id' => $serviceOrder->getKey(),
                    'image_paths' => $unusedPaths->values()->all(),
                ]);

                return $redirect->with('warning', 'Ordem de Serviço excluída. Algumas fotos não puderam ser removidas.');
            }
        }

        return $redirect->with('success', 'Ordem de Serviço excluída com sucesso!');
    }

    public function updateStatus(Request $request, ServiceOrder $serviceOrder)
    {
        $this->checkAdmin();

        $request->validate([
            'status' => ['required', Rule::in([
                ServiceOrder::STATUS_CREATED,
                ServiceOrder::STATUS_UNDER_ANALYSIS,
                ServiceOrder::STATUS_APPROVED,
                ServiceOrder::STATUS_COMPLETED,
                ServiceOrder::STATUS_CANCELLED,
            ])],
        ]);

        if ($serviceOrder->items()->whereHas('technicalSheet')->exists()) {
            return back()->with('error', 'O andamento desta OS é controlado pelas fichas técnicas.');
        }
        $serviceOrder->update(['status' => $request->status]);

        return redirect()->route('service-orders.show', $serviceOrder)
            ->with('success', 'Status da OS atualizado com sucesso!');
    }

    public function pdf(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load('items.product');

        $pdf = PDF::loadView('service-orders.pdf', compact('serviceOrder'));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("OS-{$serviceOrder->order_number}.pdf");
    }

    public function downloadPdf(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load('items.product');

        $pdf = PDF::loadView('service-orders.pdf', compact('serviceOrder'));

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('enable_html5_parser', true);
        $pdf->setOption('enable_remote', true);

        $filename = "OS-{$serviceOrder->order_number}-{$serviceOrder->order_date->format('d-m-Y')}.pdf";

        return $pdf->download($filename);
    }

    private function checkAdmin()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem realizar esta ação.');
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new ServiceOrdersExport($request), 'ordens_servico.xlsx');
    }

    public function updateItemsStatus(Request $request, ServiceOrder $serviceOrder, TechnicalSheetWorkflow $workflow)
    {
        // Valida se a ação enviada é permitida
        $request->validate([
            'action' => ['required', Rule::in(['send_store', 'deliver'])],
            'item_ids' => ['nullable', 'array'],
            'item_ids.*' => ['integer'],
        ]);

        $ids = $request->input('item_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Selecione ao menos uma peça para atualizar.');
        }

        // Só as peças selecionadas pelo usuário, e apenas as que pertencem a esta OS.
        $items = $serviceOrder->items()
            ->whereIn('id', $ids)
            ->with('technicalSheet')
            ->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Nenhuma peça válida foi selecionada.');
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($items as $item) {
            // Só processa se o item já tiver uma ficha técnica
            if ($item->technicalSheet) {
                try {
                    // Usa o Workflow para atualizar, garantindo o histórico e regras
                    $workflow->update($item, [
                        'action' => $request->action,
                        'version' => $item->technicalSheet->version, // Previne conflitos
                        'notes' => 'Atualização de status em massa pelo painel da OS.',
                    ], auth()->id());

                    $successCount++;
                } catch (\Exception $e) {
                    // Se uma peça der erro (ex: status não permite a transição), conta como erro e continua
                    Log::error("Erro ao atualizar item {$item->id} em massa: ".$e->getMessage());
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }

        if ($errorCount > 0) {
            return back()->with('warning', "{$successCount} peça(s) atualizada(s). {$errorCount} peça(s) não puderam ser atualizadas (verifique o status atual delas).");
        }

        return back()->with('success', "Status de {$successCount} peça(s) atualizado(s) com sucesso!");
    }
}
