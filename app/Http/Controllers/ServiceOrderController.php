<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\Product;
use App\Models\ServiceOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ServiceOrdersExport;
use Maatwebsite\Excel\Facades\Excel;

class ServiceOrderController extends Controller
{
    private $flaskApiUrl = 'http://localhost:9059';

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
        $query = ServiceOrder::query();

        $query->when($request->order_number, fn($q, $v) => $q->where('order_number', 'like', "%$v%"));
        $query->when($request->client_name, fn($q, $v) => $q->where('client_name', 'like', "%$v%"));
        $query->when($request->consultant_name, fn($q, $v) => $q->where('consultant_name', 'like', "%$v%"));
        $query->when($request->origin, fn($q, $v) => $q->where('origin', $v));
        $query->when($request->status, fn($q, $v) => $q->where('status', $v));

        $serviceOrders = $query->orderByDesc('id')->paginate(10);

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
            ]);

            $serviceOrderData = collect($validated)->except('items')->toArray();
            $serviceOrderData['status'] = self::STATUS_CREATED;

            $serviceOrder = ServiceOrder::create($serviceOrderData);

            if (!empty($request->items) && is_array($request->items)) {
                foreach ($request->items as $itemData) {
                    $item = new ServiceOrderItem([
                        'identified_product_name' => $itemData['product_name'] ?? null,
                        'services' => $itemData['services'] ?? null,
                        'coating' => $itemData['coating'] ?? null,
                        'observations' => $itemData['observations'] ?? null,
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
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao salvar OS: ' . $e->getMessage()]);
        }
    }

    public function analysis(ServiceOrder $serviceOrder)
    {
        if (!auth()->user()->isTechnical() && !auth()->user()->isAdmin()) {
            return redirect()->route('service-orders.show', $serviceOrder)
                ->with('error', 'Acesso permitido apenas para o setor técnico.');
        }

        $serviceOrder->load('items');

        $pendingItems = $serviceOrder->items->filter(function ($item) {
            return $item->needsAnalysis();
        });

        $analyzedItems = $serviceOrder->items->filter(function ($item) {
            return $item->hasTechnicalAnalysis();
        });

        return view('service-orders.analysis', compact('serviceOrder', 'pendingItems', 'analyzedItems'));
    }

    public function saveAnalysis(Request $request, ServiceOrder $serviceOrder)
    {

        try {
            $validated = $request->validate([
                'item_id' => 'required|exists:service_order_items,id',
                'is_guarantee' => 'required|boolean',
                'repair_price' => 'nullable|required_if:is_guarantee,false|numeric|min:0',
                'technical_status' => 'required|in:RP,SC,GTC,GCG,N',
                'technical_observations' => 'nullable|string',
            ]);

            $item = ServiceOrderItem::findOrFail($validated['item_id']);

            if ($item->service_order_id !== $serviceOrder->id) {
                return response()->json(['error' => 'Item não pertence a esta OS'], 400);
            }

            if ($validated['is_guarantee']) {
                $validated['repair_price'] = null;
            }

            $item->completeAnalysis(
                $validated['technical_status'],
                $validated['is_guarantee'],
                $validated['repair_price'],
                $validated['technical_observations']
            );

            $pendingCount = $serviceOrder->items->filter(function ($i) {
                return $i->needsAnalysis();
            })->count();

            if ($pendingCount === 0) {
                $serviceOrder->update([
                    'status' => ServiceOrder::STATUS_APPROVED,
                    'technical_analyst_id' => auth()->id(),
                    'analysis_date' => now(),
                ]);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Análise salva com sucesso!',
                    'item' => $item,
                    'pending_count' => $pendingCount
                ]);
            }

            return redirect()
                ->route('service-orders.show', $serviceOrder)
                ->with('success', 'Análise do item salva com sucesso!');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao salvar análise: ' . $e->getMessage()]);
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
            $url = rtrim($this->flaskApiUrl, '/') . '/analisar';

            $response = Http::timeout(60)
                ->attach('file', file_get_contents($tempPath), 'image.jpg')
                ->post($url, [
                    'produto' => $request->product_type,
                ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro na análise da IA: ' . $response->body(),
                ], 500);
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
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage(),
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
        $serviceOrder->load('items.product', 'technicalAnalyst');
        return view('service-orders.show', compact('serviceOrder'));
    }

    public function edit(ServiceOrder $serviceOrder)
    {
        if (!auth()->user()->isAdmin()) {
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
                'is_guarantee' => $request->has('is_guarantee'),
            ]);

            $existingItemIds = $serviceOrder->items->pluck('id')->toArray();
            $updatedItemIds = [];

            foreach ($request->items as $itemData) {
                $itemData['services'] = json_encode($itemData['services'] ?? []);
                $productName = $itemData['product_name'] ?? $itemData['identified_product_name'] ?? null;

                if (!empty($itemData['id'])) {
                    $item = $serviceOrder->items()->find($itemData['id']);

                    if ($item) {
                        $item->update([
                            'identified_product_name' => $productName,
                            'product_id' => null,
                            'coating' => $itemData['coating'] ?? null,
                            'services' => $itemData['services'],
                            'observations' => $itemData['observations'] ?? null,
                            'ai_identified_code' => $itemData['ai_identified_code'] ?? null,
                        ]);
                        $updatedItemIds[] = $item->id;
                    } else {
                        $newItem = $serviceOrder->items()->create([
                            'identified_product_name' => $productName,
                            'product_id' => null,
                            'coating' => $itemData['coating'] ?? null,
                            'services' => $itemData['services'],
                            'observations' => $itemData['observations'] ?? null,
                            'ai_identified_code' => $itemData['ai_identified_code'] ?? null,
                        ]);
                        $updatedItemIds[] = $newItem->id;
                    }
                } else {
                    $newItem = $serviceOrder->items()->create([
                        'identified_product_name' => $productName,
                        'product_id' => null,
                        'coating' => $itemData['coating'] ?? null,
                        'services' => $itemData['services'],
                        'observations' => $itemData['observations'] ?? null,
                        'ai_identified_code' => $itemData['ai_identified_code'] ?? null,
                    ]);
                    $updatedItemIds[] = $newItem->id;
                }

                if (isset($itemData['product_image']) && $itemData['product_image'] instanceof \Illuminate\Http\UploadedFile) {
                    $path = $itemData['product_image']->store('service-order-items', 'public');
                    $item->update(['product_image' => $path]);
                }
            }

            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                $serviceOrder->items()->whereIn('id', $itemsToDelete)->delete();
            }

            return redirect()->route('service-orders.show', $serviceOrder)
                ->with('success', 'Ordem de Serviço atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar ordem de serviço: ' . $e->getMessage());
        }
    }

    public function destroy(ServiceOrder $serviceOrder)
    {
        $serviceOrder->delete();

        return redirect()->route('service-orders.index')
            ->with('success', 'Ordem de Serviço excluída com sucesso!');
    }

    public function updateStatus(Request $request, ServiceOrder $serviceOrder)
    {
        $this->checkAdmin();

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

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
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem realizar esta ação.');
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new ServiceOrdersExport($request), 'ordens_servico.xlsx');
    }

    public function updateItemStatus(Request $request, ServiceOrder $serviceOrder, ServiceOrderItem $item)
    {
        if (!auth()->user()->isTechnical() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:start_repair,complete_repair,approve_quality,reject_quality',
            'observations' => 'nullable|string'
        ]);

        try {
            switch ($request->action) {
                case 'start_repair':
                    $item->startRepair();
                    $message = 'Reparo iniciado!';
                    break;
                case 'complete_repair':
                    $item->completeRepair();
                    $message = 'Reparo concluído, enviado para controle de qualidade!';
                    break;
                case 'approve_quality':
                    $item->approveQuality();
                    $message = 'Item aprovado no controle de qualidade!';
                    break;
                case 'reject_quality':
                    $item->rejectQuality($request->observations);
                    $message = 'Item reprovado, retornando para reparo!';
                    break;
            }

            return redirect()
                ->route('service-orders.show', $serviceOrder)
                ->with('success', $message);
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }
    }
}