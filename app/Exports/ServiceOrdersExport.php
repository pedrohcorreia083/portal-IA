<?php

namespace App\Exports;

use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServiceOrdersExport implements FromCollection
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = ServiceOrder::query();

        // 🔍 Filtros
        if ($this->request->order_number) {
            $query->where('order_number', 'like', '%' . $this->request->order_number . '%');
        }

        if ($this->request->client_name) {
            $query->where('client_name', 'like', '%' . $this->request->client_name . '%');
        }

        if ($this->request->consultant_name) {
            $query->where('consultant_name', 'like', '%' . $this->request->consultant_name . '%');
        }

        if ($this->request->origin) {
            $query->where('origin', $this->request->origin);
        }

        return $query->get();
    }
}
