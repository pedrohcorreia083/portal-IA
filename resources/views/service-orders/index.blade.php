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

    <!-- 🔍 FILTROS -->
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('service-orders.index') }}">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="order_number" class="form-control"
                        placeholder="Nº OS"
                        value="{{ request('order_number') }}">
                </div>

                <div class="col-md-3 mb-2">
                    <input type="text" name="client_name" class="form-control"
                        placeholder="Cliente"
                        value="{{ request('client_name') }}">
                </div>

                <div class="col-md-3 mb-2">
                    <input type="text" name="consultant_name" class="form-control"
                        placeholder="Consultor(a)"
                        value="{{ request('consultant_name') }}">
                </div>

                <div class="col-md-3 mb-2">
                    <select name="origin" class="form-control">
                        <option value="">Origem</option>
                        <option value="SHOWROOM SP" {{ request('origin') == 'SHOWROOM SP' ? 'selected' : '' }}>SHOWROOM SP</option>
                        <option value="SHOWROOM LIMEIRA" {{ request('origin') == 'SHOWROOM LIMEIRA' ? 'selected' : '' }}>SHOWROOM LIMEIRA</option>
                        <option value="SHOWROOM ON-LINE" {{ request('origin') == 'SHOWROOM ON-LINE' ? 'selected' : '' }}>SHOWROOM ON-LINE</option>
                        <option value="E-COMMERCE" {{ request('origin') == 'E-COMMERCE' ? 'selected' : '' }}>E-COMMERCE</option>
                        <option value="EVENTOS" {{ request('origin') == 'EVENTOS' ? 'selected' : '' }}>EVENTOS</option>
                    </select>
                </div>
            </div>

            <div class="mt-2 text-right">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="{{ route('service-orders.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>
        </form>
    </div>
    <!-- /FILTROS -->

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