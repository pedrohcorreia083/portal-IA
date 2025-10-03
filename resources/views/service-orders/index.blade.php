@extends('layouts.app')

@section('title', 'Ordens de Serviço')

@section('content-header')
Ordens de Serviço
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Ordens de Serviço</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Ordens de Serviço</h3>
        <div class="card-tools">
            <a href="{{ route('service-orders.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nova OS
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nº OS</th>
                    <th>Cliente</th>
                    <th>Consultor</th>
                    <th>Origem</th>
                    <th>Data</th>
                    <th>Itens</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($serviceOrders as $serviceOrder)
                <tr>
                    <td>{{ $serviceOrder->order_number }}</td>
                    <td>{{ $serviceOrder->client_name }}</td>
                    <td>{{ $serviceOrder->consultant_name }}</td>
                    <td>{{ $serviceOrder->origin }}</td>
                    <td>{{ $serviceOrder->order_date->format('d/m/Y') }}</td>
                    <td>{{ $serviceOrder->total_items }}</td>
                    <td>
                        <span class="badge badge-{{ $serviceOrder->status === 'completed' ? 'success' : ($serviceOrder->status === 'cancelled' ? 'danger' : 'warning') }}">
                            {{ ucfirst($serviceOrder->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('service-orders.show', $serviceOrder) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('service-orders.edit', $serviceOrder) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Nenhuma ordem de serviço encontrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $serviceOrders->links() }}
    </div>
</div>
@endsection