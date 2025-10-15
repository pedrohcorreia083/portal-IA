@extends('layouts.app')

@section('title', 'Editar OS: ' . $serviceOrder->order_number)

@section('content-header')
Editar Ordem de Serviço: {{ $serviceOrder->order_number }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('service-orders.index') }}">Ordens de Serviço</a></li>
<li class="breadcrumb-item"><a href="{{ route('service-orders.show', $serviceOrder) }}">{{ $serviceOrder->order_number }}</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('service-orders.update', $serviceOrder) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Dados do Cliente -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dados do Cliente</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client_name">Cliente *</label>
                                <input type="text" class="form-control" id="client_name" name="client_name"
                                    value="{{ old('client_name', $serviceOrder->client_name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="client_document">CPF/CNPJ</label>
                                <input type="text" class="form-control" id="client_document" name="client_document"
                                    value="{{ old('client_document', $serviceOrder->client_document) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="client_phone">Telefone</label>
                                <input type="text" class="form-control" id="client_phone" name="client_phone"
                                    value="{{ old('client_phone', $serviceOrder->client_phone) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="consultant_name">Consultora *</label>
                                <input type="text" class="form-control" id="consultant_name" name="consultant_name"
                                    value="{{ old('consultant_name', $serviceOrder->consultant_name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="client_code">Cód. Cliente *</label>
                                <input type="text" class="form-control" id="client_code" name="client_code"
                                    value="{{ old('client_code', $serviceOrder->client_code) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="order_date">Data *</label>
                                <input type="date" class="form-control" id="order_date" name="order_date"
                                    value="{{ old('order_date', $serviceOrder->order_date->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="origin">Origem *</label>
                                <select class="form-control" id="origin" name="origin" required>
                                    <option value="">Selecione...</option>
                                    <option value="SHOWROOM SP" {{ old('origin', $serviceOrder->origin) == 'SHOWROOM SP' ? 'selected' : '' }}>SHOWROOM SP</option>
                                    <option value="SHOWROOM LIMEIRA" {{ old('origin', $serviceOrder->origin) == 'SHOWROOM LIMEIRA' ? 'selected' : '' }}>SHOWROOM LIMEIRA</option>
                                    <option value="SHOWROOM ON-LINE" {{ old('origin', $serviceOrder->origin) == 'SHOWROOM ON-LINE' ? 'selected' : '' }}>SHOWROOM ON-LINE</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="pending" {{ old('status', $serviceOrder->status) == 'pending' ? 'selected' : '' }}>Pendente</option>
                                    <option value="in_progress" {{ old('status', $serviceOrder->status) == 'in_progress' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="completed" {{ old('status', $serviceOrder->status) == 'completed' ? 'selected' : '' }}>Concluída</option>
                                    <option value="cancelled" {{ old('status', $serviceOrder->status) == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-4">
                                    <input type="checkbox" class="custom-control-input" id="is_guarantee" name="is_guarantee" value="1"
                                        {{ old('is_guarantee', $serviceOrder->is_guarantee) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_guarantee">É Garantia?</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observations">Observações</label>
                        <textarea class="form-control" id="observations" name="observations" rows="3">{{ old('observations', $serviceOrder->observations) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Produtos e Serviços -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Produtos e Serviços</h3>
                </div>
                <div class="card-body">
                    @foreach($serviceOrder->items as $index => $item)
                    <div class="product-item border rounded p-3 mb-3">
                        <h5>Produto #{{ $index + 1 }}</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Produto</label>
                                    <input type="text" class="form-control" value="{{ $item->product->code }} - {{ $item->product->name }}" readonly>
                                    <small class="form-text text-muted">Produto não pode ser alterado</small>

                                    @if($item->product_image)
                                    <div class="mt-2">
                                        <strong>Foto:</strong><br>
                                        <img src="{{ Storage::url($item->product_image) }}" alt="Foto do produto" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Banho</label>
                                    <select class="form-control" name="items[{{ $index }}][coating]">
                                        <option value="">Selecione...</option>
                                        <option value="DO" {{ $item->coating == 'DO' ? 'selected' : '' }}>Dourado</option>
                                        <option value="RH" {{ $item->coating == 'RH' ? 'selected' : '' }}>Ródio branco</option>
                                        <option value="RO" {{ $item->coating == 'RO' ? 'selected' : '' }}>Redo</option>
                                        <option value="NE" {{ $item->coating == 'NE' ? 'selected' : '' }}>Ródio negro</option>
                                        <option value="MIX" {{ $item->coating == 'MIX' ? 'selected' : '' }}>Mix de banhos</option>
                                        <option value="CL" {{ $item->coating == 'CL' ? 'selected' : '' }}>Colorido</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Serviços *</label>
                                    <div>
                                        @php
                                        $currentServices = $item->services ?? [];
                                        @endphp
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="B"
                                                id="service-b-{{ $index }}" {{ in_array('B', $currentServices) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="service-b-{{ $index }}">B - Banhar</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="RP"
                                                id="service-rp-{{ $index }}" {{ in_array('RP', $currentServices) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="service-rp-{{ $index }}">RP - Repor pedra</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="F"
                                                id="service-f-{{ $index }}" {{ in_array('F', $currentServices) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="service-f-{{ $index }}">F - Trocar fecho</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="L"
                                                id="service-l-{{ $index }}" {{ in_array('L', $currentServices) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="service-l-{{ $index }}">L - Limpar</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="S"
                                                id="service-s-{{ $index }}" {{ in_array('S', $currentServices) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="service-s-{{ $index }}">S - Soldar</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="RM"
                                                id="service-rm-{{ $index }}" {{ in_array('RM', $currentServices) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="service-rm-{{ $index }}">RM - Remontar</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="items[{{ $index }}][status]">
                                        <option value="">Selecione...</option>
                                        <option value="RP" {{ $item->status == 'RP' ? 'selected' : '' }}>RP - Reparo realizado</option>
                                        <option value="SC" {{ $item->status == 'SC' ? 'selected' : '' }}>SC - Sem conserto</option>
                                        <option value="GTC" {{ $item->status == 'GTC' ? 'selected' : '' }}>GTC - Garantia troca física</option>
                                        <option value="GCG" {{ $item->status == 'GCG' ? 'selected' : '' }}>GCG - Garantia crédito gerado</option>
                                        <option value="N" {{ $item->status == 'N' ? 'selected' : '' }}>N - Não é LBF</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Observações do Item</label>
                            <textarea class="form-control" name="items[{{ $index }}][observations]" rows="2">{{ $item->observations }}</textarea>
                        </div>

                        <!-- Campos hidden para manter o product_id -->
                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Atualizar Ordem de Serviço
                    </button>
                    <a href="{{ route('service-orders.show', $serviceOrder) }}" class="btn btn-default">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<style>
    .product-item {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
    }
</style>
@endpush