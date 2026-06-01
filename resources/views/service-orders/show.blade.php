@extends('layouts.app')

@section('title', 'OS: ' . $serviceOrder->order_number)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('service-orders.index') }}">Ordens de Serviço</a>
    </li>
    <li class="breadcrumb-item active">{{ $serviceOrder->order_number }}</li>
@endsection

@section('content')
    <div class="container-fluid">
        {{-- ===== HEADER PRINCIPAL ===== --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary p-3 d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px;">
                    <i class="fas fa-wrench text-white fa-lg"></i>
                </div>
                <div>
                    <h3 class="mb-0 font-weight-bold">#{{ $serviceOrder->order_number }}</h3>
                    <div class="text-muted mt-1">
                        <i class="fas fa-building mr-1"></i> {{ $serviceOrder->origin }}
                        <span class="mx-2">•</span>
                        <i class="fas fa-user mr-1"></i> {{ $serviceOrder->client_name }}
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar-alt mr-1"></i> {{ $serviceOrder->order_date->format('d/m/Y') }}
                        <span class="mx-2">•</span>
                        <i class="fas fa-clock mr-1"></i> {{ $serviceOrder->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            <div class="text-right">
                <span class="badge {{ $serviceOrder->status_badge_class }} px-3 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-circle mr-1" style="font-size: 0.6rem;"></i>
                    {{ $serviceOrder->status_label }}
                </span>
                <div class="mt-1 small text-muted">
                    <i class="fas fa-sync-alt mr-1"></i> Atualizado {{ $serviceOrder->updated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <div class="row">
            {{-- ===== COLUNA PRINCIPAL (ESQUERDA) ===== --}}
            <div class="col-lg-8">



                {{-- Lista de Itens --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 font-weight-bold">
                                <i class="fas fa-list-ul text-primary mr-2"></i> Itens da Ordem de Serviço
                            </h5>
                            <span class="badge badge-light">{{ $serviceOrder->items->count() }} itens</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @forelse ($serviceOrder->items as $index => $item)
                            <div
                                class="item-card mb-3 p-3 {{ $index === $serviceOrder->items->count() - 1 ? 'mb-0' : '' }}">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                                    {{-- Informações do Item --}}
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                            <h6 class="mb-0 font-weight-bold">
                                                {{ $item->product->code ?? '' }}
                                                {{ $item->product->name ?? $item->identified_product_name }}
                                            </h6>

                                            {{-- Status do Item --}}


                                            @if ($item->coating_name && $item->coating_name !== '-')
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-tint mr-1"></i> {{ $item->coating_name }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Serviços --}}
                                        @if ($item->services_names && count($item->services_names) > 0)
                                            <div class="mb-2">
                                                @foreach ($item->services_names as $serviceName)
                                                    <span class="badge badge-outline-primary mr-1 mb-1 px-2 py-1">
                                                        <i class="fas fa-tools mr-1"></i> {{ $serviceName }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Observações --}}
                                        @if ($item->observations || $item->technical_observations)
                                            <div class="small mt-2 pt-2 border-top">
                                                @if ($item->observations)
                                                    <div class="mb-1">
                                                        <i class="fas fa-comment-alt mr-1 text-info"></i>
                                                        <strong>Observação:</strong> {{ $item->observations }}
                                                    </div>
                                                @endif
                                                @if ($item->technical_observations)
                                                    <div>
                                                        <i class="fas fa-microscope mr-1 text-warning"></i>
                                                        <strong>Técnica:</strong> {{ $item->technical_observations }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Status Técnico --}}
                                        @if ($item->technical_status)
                                            <div class="mt-2">
                                                <span
                                                    class="badge badge-{{ $item->technical_status === 'RP' ? 'success' : ($item->technical_status === 'SC' ? 'danger' : 'info') }}">
                                                    <i class="fas fa-stethoscope mr-1"></i>
                                                    {{ $item->technical_status_name }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Status e Valores --}}
                                    <div class="text-right ml-3" style="min-width: 120px;">
                                        <div class="mb-2">
                                            @if ($item->is_guarantee === true)
                                                <span class="badge badge-success px-3 py-2 d-block">
                                                    <i class="fas fa-check-circle mr-1"></i> Garantia
                                                </span>
                                            @elseif($item->is_guarantee === false)
                                                <span class="badge badge-warning px-3 py-2 d-block">
                                                    <i class="fas fa-dollar-sign mr-1"></i> Pago
                                                </span>
                                            @else
                                                <span class="badge badge-secondary px-3 py-2 d-block">
                                                    <i class="fas fa-clock mr-1"></i> Pendente
                                                </span>
                                            @endif
                                        </div>

                                        @if ($item->repair_price && $item->is_guarantee === false)
                                            <strong class="text-success h6">
                                                R$ {{ number_format($item->repair_price, 2, ',', '.') }}
                                            </strong>
                                        @elseif($item->is_guarantee === true)
                                            <small class="text-muted">Sem custo</small>
                                        @endif

                                        {{-- Botão de Ações --}}
                                        <div class="dropdown mt-2">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-toggle="dropdown">
                                                <i class="fas fa-cog mr-1"></i> Ações
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <button type="button" class="dropdown-item" data-toggle="modal"
                                                    data-target="#itemModal{{ $item->id }}">
                                                    <i class="fas fa-eye mr-2"></i> Ver Detalhes
                                                </button>

                                                <div class="dropdown-divider"></div>

                                                @if ($item->needsAnalysis() && (auth()->user()->isTechnical() || auth()->user()->isAdmin()))
                                                    <button type="button" class="dropdown-item text-primary"
                                                        data-toggle="modal"
                                                        data-target="#analysisModal{{ $item->id }}">
                                                        <i class="fas fa-microscope mr-2"></i> Análise Técnica
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ===== MODAL: Detalhes do Item ===== --}}
                            <div class="modal fade" id="itemModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-dark text-white rounded-3">
                                            <h5 class="modal-title">
                                                <i class="fas fa-box mr-2"></i>
                                                Detalhes do Item - {{ $item->identified_product_name }}
                                            </h5>
                                            <button type="button" class="close text-white"
                                                data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="text-muted mb-3 border-bottom pb-2">Informações do Produto
                                                    </h6>
                                                    <table class="table table-sm table-borderless">
                                                        <tr>
                                                            <th width="40%">Nome:</th>
                                                            <td>{{ $item->product->name ?? $item->identified_product_name }}
                                                            </td>
                                                        </tr>
                                                        @if ($item->product)
                                                            <tr>
                                                                <th>Código:</th>
                                                                <td>{{ $item->product->code ?? '-' }}</td>
                                                            </tr>
                                                        @endif
                                                        <tr>
                                                            <th>Banho:</th>
                                                            <td>{{ $item->coating_name ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Serviços:</th>
                                                            <td>
                                                                @forelse($item->services_names as $service)
                                                                    <span
                                                                        class="badge badge-info">{{ $service }}</span>
                                                                @empty -
                                                                @endforelse
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Observação:</th>
                                                            <td>{{ $item->observations ?? '-' }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-muted mb-3 border-bottom pb-2">Análise Técnica</h6>
                                                    <table class="table table-sm table-borderless">
                                                        <tr>
                                                            <th width="40%">Status Técnico:</th>
                                                            <td>
                                                                @if ($item->technical_status)
                                                                    <span
                                                                        class="badge badge-info">{{ $item->technical_status_name }}</span>
                                                                @else
                                                                    <span class="text-muted">Pendente</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Garantia:</th>
                                                            <td>
                                                                @if ($item->is_guarantee === true)
                                                                    <span class="badge badge-success">Sim</span>
                                                                @elseif($item->is_guarantee === false)
                                                                <span class="badge badge-warning">Não</span>@else<span
                                                                        class="badge badge-secondary">Pendente</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Preço Reparo:</th>
                                                            <td>
                                                                @if ($item->repair_price)
                                                                    R$
                                                                    {{ number_format($item->repair_price, 2, ',', '.') }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Obs. Técnica:</th>
                                                            <td>{{ $item->technical_observations ?? '-' }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            @if ($item->product_image)
                                                <div class="text-center mt-3">
                                                    <img src="{{ Storage::url($item->product_image) }}"
                                                        class="img-fluid rounded" style="max-height: 200px;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ===== MODAL: Análise Técnica ===== --}}
                            @if ($item->needsAnalysis())
                                <div class="modal fade" id="analysisModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('service-orders.save-analysis', $serviceOrder) }}"
                                                method="POST">
                                                @csrf
                                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                                <div class="modal-header bg-dark text-white rounded-3">
                                                    <h5 class="modal-title">Análise Técnica -
                                                        {{ $item->identified_product_name }}</h5>
                                                    <button type="button" class="close text-white"
                                                        data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>É Garantia?</label>
                                                        <div>
                                                            <label class="mr-3"><input type="radio"
                                                                    name="is_guarantee" value="1"
                                                                    onchange="document.getElementById('priceField{{ $item->id }}').style.display='none'">
                                                                Sim</label>
                                                            <label><input type="radio" name="is_guarantee"
                                                                    value="0"
                                                                    onchange="document.getElementById('priceField{{ $item->id }}').style.display='block'">
                                                                Não</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group" id="priceField{{ $item->id }}"
                                                        style="display: none;">
                                                        <label>Preço do Reparo</label>
                                                        <input type="number" step="0.01" name="repair_price"
                                                            class="form-control" placeholder="0,00">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Status Técnico</label>
                                                        <select name="technical_status" class="form-control" required>
                                                            <option value="">Selecione...</option>
                                                            <option value="RP">RP - Reparo realizado</option>
                                                            <option value="SC">SC - Sem conserto</option>
                                                            <option value="GTC">GTC - Garantia troca física</option>
                                                            <option value="GCG">GCG - Garantia crédito gerado</option>
                                                            <option value="N">N - Não é LBF</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Observações</label>
                                                        <textarea name="technical_observations" class="form-control" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Salvar Análise</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- ===== MODAL: Reprovar Qualidade ===== --}}
                            <div class="modal fade" id="rejectQualityModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form
                                            action="{{ route('service-orders.items.reject-quality', [$serviceOrder, $item]) }}"
                                            method="POST">
                                            @csrf
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Reprovar Qualidade -
                                                    {{ $item->identified_product_name }}</h5>
                                                <button type="button" class="close text-white"
                                                    data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Motivo da Reprovação</label>
                                                    <textarea name="observations" class="form-control" rows="4" required></textarea>
                                                </div>
                                                <div class="alert alert-warning">Ao reprovar, o item retornará para a etapa
                                                    de reparo.</div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-danger">Reprovar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">Nenhum item adicionado a esta OS.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===== COLUNA LATERAL (DIREITA) ===== --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-cogs text-primary mr-2"></i> Ações</h5>
                    </div>
                    <div class="card-body pt-2">
                        <div class="d-grid gap-2">
                            <a href="{{ route('service-orders.pdf', $serviceOrder) }}" target="_blank"
                                class="btn btn-success btn-block"><i class="fas fa-file-pdf mr-2"></i> Visualizar PDF</a>
                            <a href="{{ route('service-orders.download-pdf', $serviceOrder) }}"
                                class="btn btn-primary btn-block"><i class="fas fa-download mr-2"></i> Baixar PDF</a>
                            <a href="{{ route('service-orders.edit', $serviceOrder) }}"
                                class="btn btn-warning btn-block"><i class="fas fa-edit mr-2"></i> Editar OS</a>
                            @if (auth()->user()->isAdmin() && $serviceOrder->status !== 'cancelled')
                                <form action="{{ route('service-orders.destroy', $serviceOrder) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block"
                                        onclick="return confirm('Tem certeza que deseja cancelar esta OS?')"><i
                                            class="fas fa-times-circle mr-2"></i> Cancelar OS</button>
                                </form>
                            @endif
                            <a href="{{ route('service-orders.index') }}" class="btn btn-light btn-block border"><i
                                    class="fas fa-arrow-left mr-2"></i> Voltar para Lista</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .bg-primary-light {
            background-color: rgba(78, 115, 223, 0.1);
        }

        .bg-success-light {
            background-color: rgba(28, 200, 138, 0.1);
        }

        .bg-warning-light {
            background-color: rgba(246, 194, 62, 0.1);
        }

        .bg-info-light {
            background-color: rgba(54, 185, 204, 0.1);
        }

        .card {
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .item-card {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .item-card:hover {
            background: #ffffff;
            border-color: #bac8f5;
        }

        .badge {
            font-weight: 500;
            border-radius: 6px;
        }

        .badge-outline-primary {
            background: transparent;
            border: 1px solid #4e73df;
            color: #4e73df;
        }

        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
        }

        .dropdown-item:hover {
            background-color: #f8f9fc;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .bg-light-warning {
            background-color: #fff8e1;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .item-card {
                padding: 0.75rem;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        // Mostrar/esconder campo de preço no modal de análise
        document.addEventListener('DOMContentLoaded', function() {
            @foreach ($serviceOrder->items as $item)
                const radioYes{{ $item->id }} = document.querySelector(
                    '#analysisModal{{ $item->id }} input[value="1"]');
                const radioNo{{ $item->id }} = document.querySelector(
                    '#analysisModal{{ $item->id }} input[value="0"]');
                const priceField{{ $item->id }} = document.getElementById('priceField{{ $item->id }}');

                if (radioYes{{ $item->id }}) {
                    radioYes{{ $item->id }}.addEventListener('change', function() {
                        if (priceField{{ $item->id }}) priceField{{ $item->id }}.style.display =
                            'none';
                    });
                }
                if (radioNo{{ $item->id }}) {
                    radioNo{{ $item->id }}.addEventListener('change', function() {
                        if (priceField{{ $item->id }}) priceField{{ $item->id }}.style.display =
                            'block';
                    });
                }
            @endforeach
        });
    </script>
@endpush
