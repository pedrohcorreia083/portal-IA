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
                    <span class="badge badge-{{ $serviceOrder->status === 'completed' ? 'success' : ($serviceOrder->status === 'cancelled' ? 'danger' : 'warning') }}">
                        {{ ucfirst($serviceOrder->status) }}
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
                        <strong>Tipo:</strong>
                        @if($serviceOrder->is_guarantee)
                        <span class="badge badge-warning">Garantia</span>
                        @else
                        <span class="badge badge-info">Assistência Técnica</span>
                        @endif
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

        <!-- Produtos e Serviços -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Produtos e Serviços ({{ $serviceOrder->items->count() }})</h3>
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
                                <th>Status</th>
                                <th>Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceOrder->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->product->code }} - {{ $item->product->name }}</strong>
                                    @if($item->product_image)
                                    <br>
                                    <a href="{{ Storage::url($item->product_image) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-image"></i> Ver Foto
                                    </a>
                                    @endif
                                    @if($item->identified_product_name)
                                    <br>
                                    <small class="text-muted">Identificado: {{ $item->identified_product_name }}</small>
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
                                    @if($item->status)
                                    <span class="badge badge-info">{{ $item->status_name }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->observations)
                                    <small>{{ $item->observations }}</small>
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
    </div>

    <div class="col-md-4">
        <!-- Informações da OS -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações da OS</h3>
            </div>
            <div class="card-body">
                <p><strong>Número:</strong> {{ $serviceOrder->order_number }}</p>
                <p><strong>Criada em:</strong> {{ $serviceOrder->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Atualizada em:</strong> {{ $serviceOrder->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ações Rápidas</h3>
            </div>
            <div class="card-body">
                @if(auth()->user()->isAdmin())
                <!-- Alterar Status - APENAS PARA ADMINS -->
                <form action="{{ route('service-orders.update-status', $serviceOrder) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label class="font-weight-bold">Alterar Status da OS:</label>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="pending" {{ $serviceOrder->status === 'pending' ? 'selected' : '' }}>🟡 Pendente</option>
                            <option value="in_progress" {{ $serviceOrder->status === 'in_progress' ? 'selected' : '' }}>🔵 Em Andamento</option>
                            <option value="completed" {{ $serviceOrder->status === 'completed' ? 'selected' : '' }}>🟢 Concluída</option>
                            <option value="cancelled" {{ $serviceOrder->status === 'cancelled' ? 'selected' : '' }}>🔴 Cancelada</option>
                        </select>
                        <small class="form-text text-muted">Apenas administradores podem alterar o status</small>
                    </div>
                </form>
                <hr class="my-3">
                @else
                <!-- Informação do Status para Usuários Comuns -->
                <div class="alert alert-info mb-3">
                    <strong>Status atual:</strong>
                    <span class="badge badge-{{ $serviceOrder->status === 'completed' ? 'success' : ($serviceOrder->status === 'cancelled' ? 'danger' : 'warning') }} ml-2">
                        {{ ucfirst($serviceOrder->status) }}
                    </span>
                    <br>
                    <small>Entre em contato com um administrador para alterar o status.</small>
                </div>
                @endif

                <div class="d-grid gap-2">
                    <!-- Botão Imprimir - Disponível para Todos -->
                    <a href="#" class="btn btn-success btn-block">
                        <i class="fas fa-print"></i> Imprimir OS
                    </a>

                    <!-- Botão Editar - Disponível para Todos (mas a edição em si é restrita) -->
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

                    <!-- Botão Voltar - Disponível para Todos -->
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

                <h6 class="mt-2">Status:</h6>
                <small>
                    RP - Reparo realizado, SC - Sem comentário,<br>
                    GTC - Garantia troca física,<br>
                    GCG - Garantia crédito gerado, N - Não é LBF
                </small>

                <h6 class="mt-2">Banhos:</h6>
                <small>
                    DO - Dourado, RH - Ródio branco, RO - Redo,<br>
                    NE - Ródio negro, MIX - Mix, CL - Colorido
                </small>
            </div>
        </div>
    </div>
</div>
@endsection