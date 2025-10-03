<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\Product;
use App\Models\ServiceOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

                // TODO: Integrar com API de IA aqui
                // $item->identified_product_name = $this->identifyProductWithAI($path);
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
        // Proteção extra no controller
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

    // Método para integrar com IA (placeholder)
    private function identifyProductWithAI(string $imagePath): string
    {
        // TODO: Implementar integração com API de IA
        // Por enquanto, retorna um placeholder
        return "Produto identificado pela IA - " . date('Y-m-d H:i:s');
    }
}
