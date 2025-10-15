@extends('layouts.app')

@section('title', 'OS: ' . $serviceOrder->order_number)

@section('content-header')
Ordem de Serviço: {{ $serviceOrder->order_number }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('service-orders.index') }}">Ordens de Serviço</a></li>
<li class="breadcrumb-item active">Visualizar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Dados do Cliente -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados do Cliente</h3>
                <div class="card-tools">
                    <span class="badge {{ $serviceOrder->status_badge_class }}">
                        {{ $serviceOrder->status_label }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Cliente:</strong> {{ $serviceOrder->client_name }}<br>
                        <strong>CPF/CNPJ:</strong> {{ $serviceOrder->client_document ?? 'Não informado' }}<br>
                        <strong>Telefone:</strong> {{ $serviceOrder->client_phone ?? 'Não informado' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Consultora:</strong> {{ $serviceOrder->consultant_name }}<br>
                        <strong>Cód. Cliente:</strong> {{ $serviceOrder->client_code }}<br>
                        <strong>Origem:</strong> {{ $serviceOrder->origin }}
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <strong>Data:</strong> {{ $serviceOrder->order_date->format('d/m/Y') }}<br>
                        <strong>Criada em:</strong> {{ $serviceOrder->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="col-md-6">
                        @if($serviceOrder->observations)
                        <strong>Observações:</strong><br>
                        {{ $serviceOrder->observations }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Análise Técnica (Se disponível) -->
        @if($serviceOrder->isUnderAnalysis() || $serviceOrder->status === \App\Models\ServiceOrder::STATUS_APPROVED)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check text-primary"></i>
                    Análise Técnica
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Analista Técnico:</strong> {{ $serviceOrder->technicalAnalyst->name ?? 'N/A' }}<br>
                        <strong>Data da Análise:</strong> {{ $serviceOrder->analysis_date?->format('d/m/Y H:i') ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Itens em Garantia:</strong>
                        <span class="badge badge-success">{{ $serviceOrder->guarantee_items_count }}</span><br>
                        <strong>Itens Pagos:</strong>
                        <span class="badge badge-warning">{{ $serviceOrder->paid_items_count }}</span><br>
                        @if($serviceOrder->total_repair_price > 0)
                        <strong>Total a Cobrar:</strong>
                        <span class="badge badge-info">R$ {{ number_format($serviceOrder->total_repair_price, 2, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Produtos e Serviços -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Produtos e Serviços ({{ $serviceOrder->items->count() }})</h3>
                @if(auth()->user()->isTechnical() && $serviceOrder->canBeAnalyzed())
                <div class="card-tools">
                    <a href="{{ route('service-orders.analysis', $serviceOrder) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-clipboard-check"></i> Realizar Análise
                    </a>
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produto</th>
                                <th>Banho</th>
                                <th>Serviços</th>
                                <th>Garantia</th>
                                <th>Status Técnico</th>
                                <th>Preço</th>
                                <th>Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceOrder->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($item->product)
                                    <strong>{{ $item->product->code }} - {{ $item->product->name }}</strong>
                                    @elseif($item->identified_product_name)
                                    <strong>{{ $item->identified_product_name }}</strong>
                                    @else
                                    <span class="text-muted">Produto não especificado</span>
                                    @endif
                                    @if($item->ai_identified_code)
                                    <br><small class="text-muted">IA: {{ $item->ai_identified_code }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->coating)
                                    <span class="badge badge-secondary">{{ $item->coating_name }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($item->services_names as $serviceName)
                                    <span class="badge badge-primary">{{ $serviceName }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($item->is_guarantee === true)
                                    <span class="badge badge-success">Sim</span>
                                    @elseif($item->is_guarantee === false)
                                    <span class="badge badge-warning">Não</span>
                                    @else
                                    <span class="badge badge-secondary">Pendente</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->technical_status)
                                    <span class="badge badge-info" title="{{ $item->technical_status_name }}">
                                        {{ $item->technical_status }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_guarantee === false && $item->repair_price)
                                    <strong class="text-success">R$ {{ number_format($item->repair_price, 2, ',', '.') }}</strong>
                                    @elseif($item->is_guarantee === false)
                                    <span class="text-muted small">A definir</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->technical_observations)
                                    <small class="text-primary" title="Observações técnicas: {{ $item->technical_observations }}">
                                        <i class="fas fa-sticky-note"></i>
                                    </small>
                                    @endif
                                    @if($item->observations)
                                    <br><small title="Observações: {{ $item->observations }}">{{ Str::limit($item->observations, 30) }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Botão para análise técnica (se aplicável) -->
        @if(($serviceOrder->canBeAnalyzed() && (auth()->user()->isAdmin() || auth()->user()->isTechnical())))
        <div class="card">
            <div class="card-body text-center">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Esta OS está aguardando análise técnica.
                </div>
                <a href="{{ route('service-orders.analysis', $serviceOrder) }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-clipboard-check"></i> Realizar Análise Técnica
                </a>
            </div>
        </div>
        @endif

        {{-- No card header da tabela de produtos --}}
        @if($serviceOrder->canBeAnalyzed() && (auth()->user()->isAdmin() || auth()->user()->isTechnical()))
        <div class="card-tools">
            <a href="{{ route('service-orders.analysis', $serviceOrder) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-clipboard-check"></i> Realizar Análise
            </a>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Resumo da OS -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Resumo da OS</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Número:</strong><br>
                    <span class="h5 text-primary">{{ $serviceOrder->order_number }}</span>
                </div>

                <div class="mb-3">
                    <strong>Status:</strong><br>
                    <span class="badge {{ $serviceOrder->status_badge_class }} badge-lg">
                        {{ $serviceOrder->status_label }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>Análise Técnica:</strong><br>
                    @if($serviceOrder->isUnderAnalysis() || $serviceOrder->status === \App\Models\ServiceOrder::STATUS_APPROVED)
                    <span class="badge badge-success">Realizada</span><br>
                    <small class="text-muted">
                        Por: {{ $serviceOrder->technicalAnalyst->name ?? 'N/A' }}<br>
                        Em: {{ $serviceOrder->analysis_date?->format('d/m/Y H:i') ?? 'N/A' }}
                    </small>
                    @elseif($serviceOrder->canBeAnalyzed())
                    <span class="badge badge-warning">Pendente</span>
                    @else
                    <span class="badge badge-secondary">Não aplicável</span>
                    @endif
                </div>

                <div class="mb-3">
                    <strong>Atualizada em:</strong><br>
                    {{ $serviceOrder->updated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ações</h3>
            </div>
            <div class="card-body">
                @if(auth()->user()->isAdmin())
                <!-- Alterar Status - APENAS PARA ADMINS -->
                <form action="{{ route('service-orders.update-status', $serviceOrder) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label class="font-weight-bold">Alterar Status:</label>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="created" {{ $serviceOrder->status === 'created' ? 'selected' : '' }}>🟡 Criada</option>
                            <option value="under_analysis" {{ $serviceOrder->status === 'under_analysis' ? 'selected' : '' }}>🔵 Em Análise</option>
                            <option value="approved" {{ $serviceOrder->status === 'approved' ? 'selected' : '' }}>🟢 Aprovada</option>
                            <option value="completed" {{ $serviceOrder->status === 'completed' ? 'selected' : '' }}>✅ Concluída</option>
                            <option value="cancelled" {{ $serviceOrder->status === 'cancelled' ? 'selected' : '' }}>🔴 Cancelada</option>
                        </select>
                        <small class="form-text text-muted">Apenas administradores</small>
                    </div>
                </form>
                <hr class="my-3">
                @else
                <!-- Informação do Status para Usuários Comuns -->
                <div class="alert alert-info mb-3">
                    <strong>Status atual:</strong>
                    <span class="badge {{ $serviceOrder->status_badge_class }} ml-2">
                        {{ $serviceOrder->status_label }}
                    </span>
                    <br>
                    <small>Entre em contato com administração para alterações.</small>
                </div>
                @endif

                <div class="d-grid gap-2">
                    <!-- Visualizar PDF -->
                    <a href="{{ route('service-orders.pdf', $serviceOrder) }}" target="_blank" class="btn btn-success btn-block">
                        <i class="fas fa-file-pdf"></i> Visualizar PDF
                    </a>

                    <!-- Download PDF -->
                    <a href="{{ route('service-orders.download-pdf', $serviceOrder) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-download"></i> Baixar PDF
                    </a>

                    <!-- Botão Editar -->
                    <a href="{{ route('service-orders.edit', $serviceOrder) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Editar OS
                    </a>

                    <!-- Botão Cancelar - APENAS PARA ADMINS -->
                    @if(auth()->user()->isAdmin() && $serviceOrder->status !== 'cancelled')
                    <form action="{{ route('service-orders.destroy', $serviceOrder) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block"
                            onclick="return confirm('Tem certeza que deseja cancelar esta OS? Esta ação não pode ser desfeita.')">
                            <i class="fas fa-times"></i> Cancelar OS
                        </button>
                    </form>
                    @endif

                    <!-- Botão Voltar -->
                    <a href="{{ route('service-orders.index') }}" class="btn btn-default btn-block">
                        <i class="fas fa-arrow-left"></i> Voltar para Lista
                    </a>
                </div>
            </div>
        </div>

        <!-- Legenda -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Legenda</h3>
            </div>
            <div class="card-body">
                <h6>Serviços:</h6>
                <small>
                    B - Banhar, RP - Repor pedra, F - Trocar fecho,<br>
                    L - Limpar, S - Soldar, RM - Remontar
                </small>

                <h6 class="mt-2">Status Técnico:</h6>
                <small>
                    RP - Reparo realizado<br>
                    SC - Sem conserto<br>
                    GTC - Garantia troca física<br>
                    GCG - Garantia crédito gerado<br>
                    N - Não é LBF
                </small>

                <h6 class="mt-2">Banhos:</h6>
                <small>
                    DO - Dourado, RH - Ródio branco<br>
                    RO - Rose, NE - Ródio negro<br>
                    MIX - Mix, CL - Colorido
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .badge-lg {
        font-size: 0.9em;
        padding: 0.5em 0.8em;
    }
</style>
@endpush