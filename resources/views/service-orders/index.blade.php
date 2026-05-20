@extends('layouts.app')

@section('title', 'Ordens de Serviço')

@section('content-header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0">Ordens de Serviço</h1>
            <small class="text-muted">
                Gerencie todas as ordens de serviço
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('service-orders.export', request()->all()) }}" class="btn btn-success shadow-sm mr-2">
                <i class="fas fa-file-excel mr-1"></i>
                Exportar Excel
            </a>

            <a href="{{ route('service-orders.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus mr-1"></i>
                Nova OS
            </a>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">
        Ordens de Serviço
    </li>
@endsection

@section('content')

    {{-- FILTROS --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom">
            <h3 class="card-title font-weight-bold m-0">
                <i class="fas fa-filter mr-2"></i>
                Filtros
            </h3>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('service-orders.index') }}">

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label>Nº OS</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                            </div>

                            <input type="text" name="order_number" class="form-control" placeholder="Digite o número"
                                value="{{ request('order_number') }}">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Cliente</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>

                            <input type="text" name="client_name" class="form-control" placeholder="Nome do cliente"
                                value="{{ request('client_name') }}">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Consultor(a)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                            </div>

                            <input type="text" name="consultant_name" class="form-control" placeholder="Consultor"
                                value="{{ request('consultant_name') }}">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Origem</label>

                        <select name="origin" class="form-control">
                            <option value="">Todas</option>

                            <option value="SHOWROOM SP" {{ request('origin') == 'SHOWROOM SP' ? 'selected' : '' }}>
                                SHOWROOM SP
                            </option>

                            <option value="SHOWROOM LIMEIRA"
                                {{ request('origin') == 'SHOWROOM LIMEIRA' ? 'selected' : '' }}>
                                SHOWROOM LIMEIRA
                            </option>

                            <option value="SHOWROOM ON-LINE"
                                {{ request('origin') == 'SHOWROOM ON-LINE' ? 'selected' : '' }}>
                                SHOWROOM ON-LINE
                            </option>

                            <option value="E-COMMERCE" {{ request('origin') == 'E-COMMERCE' ? 'selected' : '' }}>
                                E-COMMERCE
                            </option>

                            <option value="EVENTOS" {{ request('origin') == 'EVENTOS' ? 'selected' : '' }}>
                                EVENTOS
                            </option>
                        </select>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-search mr-1"></i>
                        Filtrar
                    </button>

                    <a href="{{ route('service-orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times mr-1"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- LISTAGEM --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">

                <h3 class="card-title m-0 font-weight-bold">
                    Lista de Ordens de Serviço
                </h3>

                <span class="badge badge-secondary px-3 py-2">
                    {{ $serviceOrders->total() }} registros
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="70">#</th>
                            <th>Nº OS</th>
                            <th>Cliente</th>
                            {{-- <th>Consultor</th> --}}
                            <th>Origem</th>
                            <th>Data</th>
                            <th>Itens</th>
                            <th>Status</th>
                            <th class="text-center" width="130">
                                Ações
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($serviceOrders as $serviceOrder)
                            <tr>

                                <td>
                                    <span class="text-muted font-weight-bold">
                                        {{ $loop->iteration + ($serviceOrders->currentPage() - 1) * $serviceOrders->perPage() }}
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        {{ $serviceOrder->order_number }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $serviceOrder->client_name }}
                                </td>

                                {{-- <td>
                                    {{ $serviceOrder->consultant_name }}
                                </td> --}}

                                <td>
                                    <span class="badge badge-info px-3 py-2">
                                        {{ $serviceOrder->origin }}
                                    </span>
                                </td>

                                <td>
                                    {{ $serviceOrder->order_date->format('d/m/Y') }}
                                </td>

                                <td>
                                    <span class="badge badge-light border px-3 py-2">
                                        {{ $serviceOrder->total_items }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $serviceOrder->status_badge_class }} px-3 py-2">
                                        {{ $serviceOrder->status_label }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group shadow-sm">

                                        <a href="{{ route('service-orders.show', $serviceOrder) }}"
                                            class="btn btn-info btn-sm" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('service-orders.edit', $serviceOrder) }}"
                                            class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">

                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>

                                    <div class="text-muted">
                                        Nenhuma ordem de serviço encontrada.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($serviceOrders->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <small class="text-muted">
                        Mostrando
                        <strong>{{ $serviceOrders->firstItem() }}</strong>
                        até
                        <strong>{{ $serviceOrders->lastItem() }}</strong>
                        de
                        <strong>{{ $serviceOrders->total() }}</strong>

                        {{ $serviceOrders->total() > 1 ? 'ordens de serviço' : 'ordem de serviço' }}
                    </small>

                    <div>
                        {{ $serviceOrders->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif

    </div>

@endsection
