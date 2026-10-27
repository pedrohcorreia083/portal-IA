@extends('layouts.app')
@section('title', 'OS: ' . $serviceOrder->order_number)
@section('content-header', 'Ordem de serviço')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('service-orders.index') }}">Ordens de Serviço</a></li>
    <li class="breadcrumb-item active">{{ $serviceOrder->order_number }}</li>
@endsection
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div><h3>#{{ $serviceOrder->order_number }}</h3><p class="text-muted mb-0">{{ $serviceOrder->client_name }} · {{ $serviceOrder->origin }} · {{ $serviceOrder->order_date->format('d/m/Y') }}</p></div>
        <span class="badge {{ $serviceOrder->status_badge_class }} p-2">{{ $serviceOrder->status_label }}</span>
    </div>
    <div class="row"><div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-header"><h3 class="card-title">Peças vinculadas <span class="badge badge-light">{{ $serviceOrder->items->count() }}</span></h3></div>
            <div class="card-body pb-0"><p class="text-muted">Consulta das peças e do andamento. A análise e as movimentações são realizadas na Ficha técnica.</p></div>
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead><tr><th>Peça</th><th>Acabamento recebido</th><th>Status da peça</th><th></th></tr></thead>
                <tbody>@forelse ($serviceOrder->items as $item)
                    <tr><td><strong>{{ $item->product->name ?? $item->identified_product_name ?? 'Peça sem nome' }}</strong><small class="d-block text-muted">#{{ $item->id }} · {{ $item->product->code ?? $item->ai_identified_code ?? 'Sem código' }}</small></td>
                        <td>{{ $item->coating_name ?? 'Não informado' }}</td>
                        <td><span class="badge badge-{{ $item->technicalSheet?->status_color ?? 'secondary' }}">{{ $item->technicalSheet?->status_label ?? 'Aguardando triagem' }}</span></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('technical-sheets.show', $item) }}">Ficha técnica</a></td></tr>
                @empty<tr><td colspan="4" class="text-center text-muted py-4">Nenhuma peça vinculada.</td></tr>@endforelse</tbody>
            </table></div>
        </div>
        <div class="card shadow-sm"><div class="card-body"><strong>Consultor(a):</strong> {{ $serviceOrder->consultant_name }}<br><strong>Observações da OS:</strong><p class="mb-0" style="white-space:pre-wrap">{{ $serviceOrder->observations ?: 'Não informadas' }}</p></div></div>
    </div><div class="col-lg-3"><div class="card shadow-sm"><div class="card-header"><h3 class="card-title">Ações da OS</h3></div><div class="card-body">
        <a href="{{ route('technical-sheets.index', ['order_number' => $serviceOrder->order_number]) }}" class="btn btn-primary btn-block">Fichas desta OS</a>
        <a href="{{ route('service-orders.pdf', $serviceOrder) }}" target="_blank" rel="noopener" class="btn btn-success btn-block">Visualizar PDF</a>
        <a href="{{ route('service-orders.download-pdf', $serviceOrder) }}" class="btn btn-outline-success btn-block">Baixar PDF</a>
        @if (auth()->user()->isAdmin())<a href="{{ route('service-orders.edit', $serviceOrder) }}" class="btn btn-outline-secondary btn-block">Editar dados da OS</a>@endif
        <a href="{{ route('service-orders.index') }}" class="btn btn-light btn-block">Voltar para lista</a>
    </div></div></div></div>
@endsection
