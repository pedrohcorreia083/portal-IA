<?php

namespace App\Exports;

use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServiceOrdersExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = ServiceOrder::query()
            ->with('products');

        // Filtro por número da OS
        if ($this->request->order_number) {
            $query->where(
                'order_number',
                'like',
                '%' . $this->request->order_number . '%'
            );
        }

        // Filtro por cliente
        if ($this->request->client_name) {
            $query->where(
                'client_name',
                'like',
                '%' . $this->request->client_name . '%'
            );
        }

        // Filtro por consultor
        if ($this->request->consultant_name) {
            $query->where(
                'consultant_name',
                'like',
                '%' . $this->request->consultant_name . '%'
            );
        }

        // Filtro por filial
        if ($this->request->origin) {
            $query->where(
                'origin',
                $this->request->origin
            );
        }

        // Filtro por data inicial
        if ($this->request->date_from) {
            $query->whereDate(
                'order_date',
                '>=',
                $this->request->date_from
            );
        }

        // Filtro por data final
        if ($this->request->date_to) {
            $query->whereDate(
                'order_date',
                '<=',
                $this->request->date_to
            );
        }

        $orders = $query->get();

        $rows = new Collection();

        foreach ($orders as $order) {

            foreach ($order->products as $product) {

                $rows->push([
                    'N° OS' => $order->order_number,
                    'Cliente' => $order->client_name,
                    'CNPJ' => $order->client_document,
                    'Vendedora' => $order->consultant_name,
                    'Filial' => $order->origin,
                    'Código cliente' => $order->client_code,
                    'Data abertura' => $order->order_date,
                    'Data fechamento' => null,

                    'Código do produto' => $product->ai_identified_code,
                    'Produto' => $product->identified_product_name,
                    'Serviço' => $product->services,
                    'Observação' => $product->observations,
                    'Preço do reparo' => $product->repair_price,

                    'Data da compra' => null,
                    'Valor de compra' => null,

                    'Status técnico' => $product->technical_status,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'N° OS',
            'Cliente',
            'CNPJ',
            'Vendedora',
            'Filial',
            'Código cliente',
            'Data abertura',
            'Data fechamento',
            'Código do produto',
            'Produto',
            'Serviço',
            'Observação',
            'Preço do reparo',
            'Data da compra',
            'Valor de compra',
            'Status técnico',
        ];
    }
}
