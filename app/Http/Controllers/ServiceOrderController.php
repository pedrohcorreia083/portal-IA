<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\Product;
use App\Models\ServiceOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

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
                'items' => 'required|array|min:1',
                'items.*.product_name' => 'required|string|max:255',
                'items.*.coating' => 'nullable|in:DO,RH,RO,NE,MIX,CL',
                'items.*.observations' => 'nullable|string',
                'items.*.product_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'items.*.ai_identified_code' => 'nullable|string',
                // REMOVIDOS: is_guarantee, repair_price, status
            ]);

            // Status inicial automático
            $serviceOrderData = collect($validated)->except('items')->toArray();
            $serviceOrderData['status'] = self::STATUS_CREATED; // ← ADICIONAR ESTA LINHA

            $serviceOrder = ServiceOrder::create($serviceOrderData);

            foreach ($request->items as $itemData) {
                $item = new ServiceOrderItem([
                    'identified_product_name' => $itemData['product_name'],
                    'product_id'   => $item['product_id'] ?? null,
                    'services' => $itemData['services'],
                    'coating' => $itemData['coating'] ?? null,
                    'observations' => $itemData['observations'] ?? null,
                    'ai_identified_code' => $itemData['ai_identified_code'] ?? null,
                    // Campos técnicos ficam NULL inicialmente
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

            return redirect()
                ->route('service-orders.show', $serviceOrder)
                ->with('success', 'Ordem de Serviço criada com sucesso! Aguardando análise técnica.');
        } catch (\Throwable $e) {
            Log::error('Erro ao salvar OS', [
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao salvar OS: ' . $e->getMessage()]);
        }
    }

    // NOVO MÉTODO: Tela de análise técnica
    public function analysis(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load('items.product');
        return view('service-orders.analysis', compact('serviceOrder'));
    }

    // NOVO MÉTODO: Salvar análise técnica
    public function saveAnalysis(Request $request, ServiceOrder $serviceOrder)
    {
        if (!auth()->user()->isTechnical()) {
            return redirect()->route('service-orders.show', $serviceOrder)
                ->with('error', 'Acesso permitido apenas para o setor técnico.');
        }

        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|exists:service_order_items,id',
                'items.*.is_guarantee' => 'required|boolean',
                'items.*.repair_price' => 'nullable|required_if:items.*.is_guarantee,false|numeric|min:0',
                'items.*.technical_status' => 'required|in:RP,SC,GTC,GCG,N',
                'items.*.technical_observations' => 'nullable|string',
            ]);

            foreach ($request->items as $itemData) {
                $item = ServiceOrderItem::find($itemData['id']);

                if ($item && $item->service_order_id === $serviceOrder->id) {
                    $item->update([
                        'is_guarantee' => $itemData['is_guarantee'],
                        'repair_price' => $itemData['is_guarantee'] ? null : $itemData['repair_price'],
                        'technical_status' => $itemData['technical_status'],
                        'technical_observations' => $itemData['technical_observations'] ?? null,
                    ]);
                }
            }

            // Atualizar status da OS
            $serviceOrder->update([
                'status' => ServiceOrder::STATUS_UNDER_ANALYSIS,
                'technical_analyst_id' => auth()->id(),
                'analysis_date' => now(),
            ]);

            return redirect()
                ->route('service-orders.show', $serviceOrder)
                ->with('success', 'Análise técnica salva com sucesso!');
        } catch (\Throwable $e) {
            Log::error('Erro ao salvar análise técnica', [
                'service_order_id' => $serviceOrder->id,
                'error' => $e->getMessage()
            ]);

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
                'product_type' => 'required|string', // categoria principal
            ]);

            $tempPath = $request->file('image')->getPathname();

            $response = Http::timeout(5000)->attach(
                'file',
                file_get_contents($tempPath),
                'image.jpg'
            )->post($this->flaskApiUrl . '/analisar', [
                'produto' => $request->product_type, // apenas categoria principal
            ]);

            if ($response->successful()) {
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
                    'similarity' => $bestMatch ? round($bestMatch['similaridade'] * 100, 1) : 0
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro na análise da IA: ' . $response->body()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erro na identificação por IA', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage()
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

        // $products = Product::where('is_active', true)->get();
        //         dd($products);

        $serviceOrder->load('items');

        return view('service-orders.edit', compact('serviceOrder'));
    }

    public function update(Request $request, ServiceOrder $serviceOrder)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('service-orders.show', $serviceOrder)
                ->with('error', 'Acesso negado. Apenas administradores podem editar ordens de serviço.');
        }

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_document' => 'nullable|string|max:20',
            'client_phone' => 'nullable|string|max:20',
            'consultant_name' => 'required|string|max:255',
            'client_code' => 'required|string|max:50',
            'origin' => 'required|in:SHOWROOM SP,SHOWROOM LIMEIRA,SHOWROOM ON-LINE,E-COMMERCE,EVENTOS',
            'order_date' => 'required|date',
            'is_guarantee' => 'boolean',
            'observations' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $serviceOrder->update($validated);

        return redirect()->route('service-orders.show', $serviceOrder)
            ->with('success', 'Ordem de Serviço atualizada com sucesso!');
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

    // PDF Methods
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
}
