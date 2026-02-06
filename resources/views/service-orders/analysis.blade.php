@extends('layouts.app')

@section('title', 'Análise Técnica - OS #' . $serviceOrder->id)

@section('content-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Análise Técnica</h1>
            <small>OS #{{ $serviceOrder->order_number }} - {{ $serviceOrder->client_name }} </small>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('service-orders.index') }}">Ordens de Serviço</a></li>
    <li class="breadcrumb-item"><a href="{{ route('service-orders.show', $serviceOrder) }}">OS
            #{{ $serviceOrder->order_number }}</a></li>
    <li class="breadcrumb-item active">Análise Técnica</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('service-orders.save-analysis', $serviceOrder) }}" method="POST" id="analysisForm">
                @csrf

                <!-- Dados da OS -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Dados da Ordem de Serviço</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Cliente:</strong> {{ $serviceOrder->client_name }}<br>
                                <strong>Cód. Cliente:</strong> {{ $serviceOrder->client_code }}
                            </div>
                            <div class="col-md-4">
                                <strong>Consultora:</strong> {{ $serviceOrder->consultant_name }}<br>
                                <strong>Origem:</strong> {{ $serviceOrder->origin }}
                            </div>
                            <div class="col-md-4">
                                <strong>Data:</strong> {{ $serviceOrder->order_date->format('d/m/Y') }}<br>
                                <strong>Telefone:</strong> {{ $serviceOrder->client_phone ?? 'N/A' }}
                            </div>
                        </div>
                        @if ($serviceOrder->observations)
                            <div class="row mt-2">
                                <div class="col-12">
                                    <strong>Observações:</strong> {{ $serviceOrder->observations }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Análise dos Produtos -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Análise dos Produtos</h3>
                        <div class="card-tools">
                            <small class="text-muted">{{ $serviceOrder->items->count() }} produto(s)</small>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach ($serviceOrder->items as $index => $item)
                            <div class="product-analysis-item border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-11">
                                        <h5 class="mb-3">
                                            Produto{{ $index + 1 }} -
                                            {{ $item->identified_product_name ?? 'Produto não encontrado' }}
                                        </h5>
                                    </div>
                                    <div class="col-md-1 text-right">
                                        @if ($item->product_image)
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                                data-target="#imageModal{{ $item->id }}">
                                                <i class="fas fa-image"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Dados do Produto -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Serviços Solicitados:</strong><br>
                                        <div class="mt-1">
                                            @php
                                                // Usar o accessor services_names para obter os nomes dos serviços
                                                $serviceNames = $item->services_names ?? [];

                                                // Se não houver serviços, mostrar mensagem
                                                if (empty($serviceNames)) {
                                                    $serviceNames = ['Nenhum serviço especificado'];
                                                }
                                            @endphp

                                            @foreach ($serviceNames as $serviceName)
                                                <span class="badge badge-secondary">{{ $serviceName }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Banho:</strong>
                                        {{ $item->coating_name ?: 'Não especificado' }}
                                    </div>
                                </div>

                                @if ($item->observations)
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <strong>Observações da Consultora:</strong>
                                            <div class="alert alert-light border mt-1">
                                                {{ $item->observations }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Campos de Análise Técnica -->
                                <div class="technical-analysis bg-light rounded p-3">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-clipboard-check"></i>
                                        Análise Técnica
                                    </h6>

                                    <div class="row">
                                        <!-- Garantia -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="is_guarantee_{{ $item->id }}">
                                                    <strong>É Garantia? *</strong>
                                                </label>
                                                <div class="mt-1">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input guarantee-radio" type="radio"
                                                            name="items[{{ $index }}][is_guarantee]"
                                                            id="guarantee_yes_{{ $item->id }}" value="1"
                                                            {{ old("items.{$index}.is_guarantee", $item->is_guarantee) === true ? 'checked' : '' }}
                                                            required>
                                                        <label class="form-check-label text-success"
                                                            for="guarantee_yes_{{ $item->id }}">
                                                            <i class="fas fa-check-circle"></i> Sim
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input guarantee-radio" type="radio"
                                                            name="items[{{ $index }}][is_guarantee]"
                                                            id="guarantee_no_{{ $item->id }}" value="0"
                                                            {{ old("items.{$index}.is_guarantee", $item->is_guarantee) === false ? 'checked' : '' }}
                                                            required>
                                                        <label class="form-check-label text-danger"
                                                            for="guarantee_no_{{ $item->id }}">
                                                            <i class="fas fa-times-circle"></i> Não
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Preço (condicional) -->
                                        <div class="col-md-4">
                                            <div class="form-group price-field" id="price_field_{{ $item->id }}"
                                                style="display: {{ old("items.{$index}.is_guarantee", $item->is_guarantee) === false ? 'block' : 'none' }};">
                                                <label for="repair_price_{{ $item->id }}">
                                                    <strong>Preço do Conserto (R$) *</strong>
                                                </label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">R$</span>
                                                    </div>
                                                    <input type="number" class="form-control repair-price"
                                                        id="repair_price_{{ $item->id }}"
                                                        name="items[{{ $index }}][repair_price]"
                                                        value="{{ old("items.{$index}.repair_price", $item->repair_price) }}"
                                                        step="0.01" min="0" placeholder="0,00"
                                                        {{ old("items.{$index}.is_guarantee", $item->is_guarantee) === false ? 'required' : '' }}>
                                                </div>
                                                <small class="form-text text-muted">
                                                    Preço a ser cobrado pelo conserto
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Status Técnico -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="technical_status_{{ $item->id }}">
                                                    <strong>Status Técnico *</strong>
                                                </label>
                                                <select class="form-control technical-status"
                                                    id="technical_status_{{ $item->id }}"
                                                    name="items[{{ $index }}][technical_status]" required>
                                                    <option value="">Selecione...</option>
                                                    <option value="RP"
                                                        {{ old("items.{$index}.technical_status", $item->technical_status) == 'RP' ? 'selected' : '' }}>
                                                        RP - Reparo realizado
                                                    </option>
                                                    <option value="SC"
                                                        {{ old("items.{$index}.technical_status", $item->technical_status) == 'SC' ? 'selected' : '' }}>
                                                        SC - Sem conserto
                                                    </option>
                                                    <option value="GTC"
                                                        {{ old("items.{$index}.technical_status", $item->technical_status) == 'GTC' ? 'selected' : '' }}>
                                                        GTC - Garantia troca física
                                                    </option>
                                                    <option value="GCG"
                                                        {{ old("items.{$index}.technical_status", $item->technical_status) == 'GCG' ? 'selected' : '' }}>
                                                        GCG - Garantia crédito gerado
                                                    </option>
                                                    <option value="N"
                                                        {{ old("items.{$index}.technical_status", $item->technical_status) == 'N' ? 'selected' : '' }}>
                                                        N - Não é LBF
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Observações Técnicas -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="technical_observations_{{ $item->id }}">
                                                    <strong>Observações Técnicas</strong>
                                                </label>
                                                <textarea class="form-control" id="technical_observations_{{ $item->id }}"
                                                    name="items[{{ $index }}][technical_observations]" rows="2"
                                                    placeholder="Descreva detalhes técnicos, problemas encontrados, etc...">{{ old("items.{$index}.technical_observations", $item->technical_observations) }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="items[{{ $index }}][id]"
                                        value="{{ $item->id }}">
                                </div>
                            </div>

                            <!-- Modal para imagem do produto -->
                            @if ($item->product_image)
                                <div class="modal fade" id="imageModal{{ $item->id }}" tabindex="-1"
                                    role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Foto do Produto</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ Storage::disk('public')->url($item->product_image) }}"
                                                    class="img-fluid rounded"
                                                    alt="Foto do produto {{ $item->product->code ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($serviceOrder->items->isEmpty())
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle"></i>
                                Nenhum produto encontrado nesta ordem de serviço.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Resumo e Ações -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Resumo da análise:</strong>
                                    <span id="analysisSummary">
                                        @php
                                            $guaranteeCount = $serviceOrder->items
                                                ->where('is_guarantee', true)
                                                ->count();
                                            $paidCount = $serviceOrder->items->where('is_guarantee', false)->count();
                                            $pendingCount = $serviceOrder->items->where('is_guarantee', null)->count();
                                        @endphp
                                        {{ $guaranteeCount }} em garantia, {{ $paidCount }} pagos, {{ $pendingCount }}
                                        pendentes
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="submit" class="btn btn-success btn-lg" id="saveAnalysisBtn">
                                    <i class="fas fa-save"></i> Salvar Análise
                                </button>
                                <a href="{{ route('service-orders.show', $serviceOrder) }}" class="btn btn-default">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .product-analysis-item {
            border-left: 4px solid #007bff !important;
            background: white;
            transition: all 0.3s ease;
        }

        .product-analysis-item:hover {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .technical-analysis {
            border: 1px solid #dee2e6;
            background: #f8f9fa !important;
        }

        .guarantee-radio:checked+label {
            font-weight: bold;
        }

        .price-field {
            transition: all 0.3s ease;
        }

        .repair-price:invalid {
            border-color: #dc3545;
        }

        .technical-status {
            font-weight: 500;
        }

        /* Destaque para produtos sem análise */
        .product-analysis-item:has(.technical-status[value=""]) {
            border-left-color: #ffc107 !important;
            background-color: #fffaf0;
        }

        /* Destaque para produtos com garantia */
        .product-analysis-item:has(#guarantee_yes_:checked) {
            border-left-color: #28a745 !important;
        }

        /* Destaque para produtos pagos */
        .product-analysis-item:has(#guarantee_no_:checked) {
            border-left-color: #dc3545 !important;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Controle de exibição do campo de preço
            document.querySelectorAll('.guarantee-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const itemId = this.id.split('_').pop();
                    const priceField = document.getElementById(`price_field_${itemId}`);
                    const priceInput = document.getElementById(`repair_price_${itemId}`);

                    if (this.value === '0') {
                        // Não é garantia - mostrar campo de preço
                        priceField.style.display = 'block';
                        priceInput.required = true;
                    } else {
                        // É garantia - esconder campo de preço
                        priceField.style.display = 'none';
                        priceInput.required = false;
                        priceInput.value = '';
                    }

                    updateAnalysisSummary();
                });
            });

            // Atualizar resumo da análise
            function updateAnalysisSummary() {
                const guaranteeCount = document.querySelectorAll('.guarantee-radio[value="1"]:checked').length;
                const paidCount = document.querySelectorAll('.guarantee-radio[value="0"]:checked').length;
                const totalCount = document.querySelectorAll('.product-analysis-item').length;
                const pendingCount = totalCount - (guaranteeCount + paidCount);

                const summary = document.getElementById('analysisSummary');
                if (summary) {
                    summary.textContent =
                        `${guaranteeCount} em garantia, ${paidCount} pagos, ${pendingCount} pendentes`;
                }
            }

            // Validação do formulário
            document.getElementById('analysisForm').addEventListener('submit', function(e) {
                let isValid = true;
                const errorMessages = [];

                // Verificar se todos os produtos têm análise completa
                document.querySelectorAll('.product-analysis-item').forEach(item => {
                    const guaranteeSelected = item.querySelector('.guarantee-radio:checked');
                    const statusSelected = item.querySelector('.technical-status').value;
                    const priceInput = item.querySelector('.repair-price');

                    if (!guaranteeSelected) {
                        isValid = false;
                        errorMessages.push('• Defina se é garantia para todos os produtos');
                    }

                    if (!statusSelected) {
                        isValid = false;
                        errorMessages.push('• Defina o status técnico para todos os produtos');
                    }

                    if (guaranteeSelected && guaranteeSelected.value === '0' && !priceInput.value) {
                        isValid = false;
                        errorMessages.push('• Informe o preço para produtos que não são garantia');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    const uniqueMessages = [...new Set(errorMessages)];
                    alert('Por favor, corrija os seguintes erros:\n\n' + uniqueMessages.join('\n'));
                } else {
                    // Mostrar loading no botão
                    const submitBtn = document.getElementById('saveAnalysisBtn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
                }
            });

            // Inicializar resumo
            updateAnalysisSummary();

            // Atualizar resumo quando qualquer campo mudar
            document.querySelectorAll('.guarantee-radio, .technical-status').forEach(element => {
                element.addEventListener('change', updateAnalysisSummary);
            });
        });
    </script>
@endpush
