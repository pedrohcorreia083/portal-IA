<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\Product;
use App\Models\ServiceOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $serviceOrders = ServiceOrder::with('items')->latest()->paginate(10);
        return view('service-orders.index', compact('serviceOrders'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get();
        return view('service-orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_document' => 'nullable|string|max:20',
            'client_phone' => 'nullable|string|max:20',
            'consultant_name' => 'required|string|max:255',
            'client_code' => 'required|string|max:50',
            'origin' => 'required|in:SHOWROOM SP,SHOWROOM LIMEIRA,SHOWROOM ON-LINE',
            'order_date' => 'required|date',
            'is_guarantee' => 'boolean',
            'observations' => 'nullable|string',

            // Itens da OS
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.services' => 'required|array|min:1',
            'items.*.coating' => 'nullable|in:DO,RH,RO,NE,MIX,CL',
            'items.*.status' => 'nullable|in:RP,SC,GTC,GCG,N',
            'items.*.observations' => 'nullable|string',
            'items.*.product_image' => 'nullable|image|max:2048',
        ]);

        // Criar a Ordem de Serviço
        $serviceOrder = ServiceOrder::create($validated);

        // Processar itens
        foreach ($request->items as $itemData) {
            $item = new ServiceOrderItem($itemData);

            // Upload da imagem se existir
            if (isset($itemData['product_image']) && $itemData['product_image']->isValid()) {
                $path = $itemData['product_image']->store('product-images', 'public');
                $item->product_image = $path;

                // Integração com IA (fake para demo)
                $item->identified_product_name = $this->identifyProductWithAI($path);
            }

            $serviceOrder->items()->save($item);
        }

        return redirect()->route('service-orders.show', $serviceOrder)
            ->with('success', 'Ordem de Serviço criada com sucesso!');
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load('items.product');
        return view('service-orders.show', compact('serviceOrder'));
    }

    public function edit(ServiceOrder $serviceOrder)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('service-orders.show', $serviceOrder)
                ->with('warning', 'Apenas administradores podem editar ordens de serviço.');
        }

        $products = Product::where('is_active', true)->get();
        $serviceOrder->load('items');
        return view('service-orders.edit', compact('serviceOrder', 'products'));
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
            'origin' => 'required|in:SHOWROOM SP,SHOWROOM LIMEIRA,SHOWROOM ON-LINE',
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
        $pdf->setOption('enable_html5_parser', true);
        $pdf->setOption('enable_remote', true);
        
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

    // APENAS UMA FUNÇÃO identifyProductWithAI
    private function identifyProductWithAI($imagePath)
    {
        // EM PRODUÇÃO: Aqui você integraria com uma API real de IA
        // Para demo, retorna um código aleatório
        
        $productCodes = [
            'AN01050108RH', 'BR02031506DO', 'CO03020812NE', 'PU04011018CL',
            'TO05042520RO', 'PE06031215RH', 'AL07051822DO', 'CH08022025MIX',
            'AN01060109RO', 'BR02041507NE', 'CO03030813DO', 'PU04021019RH'
        ];
        
        $randomCode = $productCodes[array_rand($productCodes)];
        
        return $randomCode;
    }

    private function checkAdmin()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem realizar esta ação.');
        }
    }
}