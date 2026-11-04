@extends('layouts.app')
@section('title', 'Ficha técnica')
@section('content-header', 'Ficha técnica')
@section('breadcrumb')
    <li class="breadcrumb-item active">Ficha técnica</li>
@endsection
@section('content')
    <p class="text-muted">Peças recebidas nas ordens de serviço. Acompanhe a triagem e o andamento individual.</p>
    <form method="GET" action="{{ route('technical-sheets.index') }}" class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach (['order_number' => 'Nº OS', 'piece' => 'Peça / código', 'client_name' => 'Cliente', 'origin' => 'Origem'] as $field => $label)
                    <div class="form-group col-md-3"><label for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}"
                            name="{{ $field }}" value="{{ request($field) }}" class="form-control" maxlength="255"></div>
                @endforeach
                <div class="form-group col-md-6"><label for="status">Etapa</label><select id="status" name="status"
                        class="form-control">
                        <option value="">Todas as etapas</option>
                        @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
                    </select></div>
                <div class="col-md-6 d-flex align-items-end justify-content-end mb-3"><button
                        class="btn btn-primary mr-2">Filtrar</button><a href="{{ route('technical-sheets.index') }}"
                        class="btn btn-outline-secondary">Limpar</a></div>
            </div>
        </div>
    </form>
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title">Peças das ordens de serviço</h3><span
                class="badge badge-secondary ml-auto">{{ $items->total() }} peças</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Peça</th>
                        <th>OS / cliente</th>
                        <th>Origem</th>
                        <th>Garantia</th>
                        <th>Etapa</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>@forelse ($items as $item)
                    <tr>
                        <td><strong>{{ $item->product->name ?? $item->identified_product_name ?? 'Peça sem nome' }}</strong><small
                                class="d-block text-muted">#{{ $item->id }} ·
                                {{ $item->product->code ?? $item->ai_identified_code ?? 'Sem código' }} ·
                                {{ $item->coating_name ?? 'Sem acabamento' }}</small></td>
                        <td><a
                                href="{{ route('service-orders.show', $item->serviceOrder) }}">{{ $item->serviceOrder->order_number }}</a><small
                                class="d-block">{{ $item->serviceOrder->client_name }}</small></td>
                        <td>{{ $item->serviceOrder->origin }}</td>
                        <td>{{ ($item->technicalSheet?->is_guarantee ?? $item->is_guarantee) === null ? 'A avaliar' : (($item->technicalSheet?->is_guarantee ?? $item->is_guarantee) ? 'Sim' : 'Não') }}
                        </td>
                        <td><span
                                class="badge badge-{{ $item->technicalSheet?->status_color ?? 'secondary' }}">{{ $item->technicalSheet?->status_label ?? 'Aguardando triagem' }}</span>@if ($item->serviceOrder->status === 'cancelled')<small
                                class="text-danger d-block">OS cancelada</small>@endif</td>
                        <td><a class="btn btn-sm btn-outline-primary text-nowrap"
                                href="{{ route('technical-sheets.show', $item) }}">Abrir ficha</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="6" class="text-center text-muted py-5">Nenhuma peça encontrada para os filtros
                            selecionados.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $items->links('pagination::bootstrap-4') }}</div>
    </div>
@endsection