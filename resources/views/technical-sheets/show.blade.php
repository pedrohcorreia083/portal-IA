@extends('layouts.app')
@section('title', 'Ficha técnica #' . $item->id)

@section('content-header')
    <div class="eyebrow">Ficha técnica #{{ $item->id }}</div>
    <h1 class="page-title">{{ $item->product->name ?? $item->identified_product_name ?? 'Peça sem nome' }}</h1>
    <p class="page-subtitle">
        <a href="{{ route('service-orders.show', $item->serviceOrder) }}">{{ $item->serviceOrder->order_number }}</a>
        · {{ $item->serviceOrder->client_name }} · {{ $item->serviceOrder->origin }}
    </p>
@endsection

@section('page-actions')
    <span class="badge badge-{{ $sheet->status_color }}">{{ $sheet->status_label }}</span>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('technical-sheets.index') }}">Ficha técnica</a></li>
    <li class="breadcrumb-item active">Peça #{{ $item->id }}</li>
@endsection
@section('content')
    @if ($item->serviceOrder->status === 'cancelled')
    <div class="alert alert-warning">Esta OS está cancelada. A ficha está disponível somente para consulta.</div>@endif
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Peça recebida</h3>
                </div>
                <div class="card-body">
                    @if ($item->product_image)<img src="{{ asset('storage/' . $item->product_image) }}"
                    class="img-fluid rounded mb-3" style="max-height:240px" alt="Foto da peça recebida">@endif
                    <dl>
                        <dt>Código</dt>
                        <dd>{{ $item->product->code ?? $item->ai_identified_code ?? 'Não informado' }}</dd>
                        <dt>Acabamento original</dt>
                        <dd>{{ $item->coating_name ?? 'Não informado' }}</dd>
                        <dt>Data da compra</dt>
                        <dd>{{ $item->purchase_date?->format('d/m/Y') ?? 'Não informada' }}</dd>
                        <dt>Valor da peça</dt>
                        <dd>{{ $item->piece_value !== null ? 'R$ ' . number_format((float) $item->piece_value, 2, ',', '.') : 'Não informado' }}</dd>
                        <dt>Serviços solicitados na OS</dt>
                        <dd>{{ implode(', ', $item->services_names) ?: 'Não informados' }}</dd>
                        <dt>Observações do recebimento</dt>
                        <dd style="white-space:pre-wrap">{{ $item->observations ?: 'Não informadas' }}</dd>
                        <dt>Consultor(a)</dt>
                        <dd>{{ $item->serviceOrder->consultant_name }}</dd>
                        @if (!$sheet->exists && $item->technical_status)
                            <dt>Resultado técnico anterior</dt>
                        <dd>{{ $item->technical_status_name }}</dd>@endif
                    </dl>
                    <a href="{{ route('service-orders.show', $item->serviceOrder) }}"
                        class="btn btn-outline-secondary btn-block">Consultar OS</a><a
                        href="{{ route('technical-sheets.index') }}" class="btn btn-light btn-block">Voltar para fichas</a>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Etapas do atendimento</h3>
                </div>
                <div class="card-body small">
                    <p>Triagem → Orientação / orçamento → Serviço ou troca → Inspeção → Encerramento → Entrega.</p>
                    <p class="mb-0 text-muted">Serviços pagos aguardam aprovação do cliente. A reprovação na inspeção
                        devolve a peça para serviço. Crédito segue um encerramento próprio.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-microscope mr-2"></i>Triagem e encaminhamento</h3>
                </div>
                <div class="card-body">
                    @if (auth()->user()->isTechnical() && $item->serviceOrder->status !== 'cancelled' && in_array($sheet->status, ['pending', 'in_analysis']))
                        <form method="POST" action="{{ route('technical-sheets.update', $item) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="triage"><input type="hidden" name="version"
                                value="{{ $sheet->version }}">
                            <div class="form-group"><label for="diagnosis">Diagnóstico técnico</label><textarea name="diagnosis"
                                    id="diagnosis" rows="3" class="form-control" required
                                    maxlength="10000">{{ old('diagnosis', $sheet->diagnosis) }}</textarea></div>
                            <div class="row">
                                <div class="form-group col-md-6"><label for="is_guarantee">Cobertura de garantia</label><select
                                        name="is_guarantee" id="is_guarantee" class="form-control" required>
                                        <option value="">Selecione</option>
                                        <option value="1" @selected((string) old('is_guarantee', $sheet->is_guarantee === null ? '' : (int) $sheet->is_guarantee) === '1')>Com garantia</option>
                                        <option value="0" @selected((string) old('is_guarantee', $sheet->is_guarantee === null ? '' : (int) $sheet->is_guarantee) === '0')>Sem garantia</option>
                                    </select></div>
                                <div class="form-group col-md-6"><label for="treatment">Tratamento indicado</label><select
                                        name="treatment" id="treatment" class="form-control" required>
                                        <option value="">Selecione</option>
                                        @foreach (\App\Models\TechnicalSheet::TREATMENTS as $value => $label)
                                            <option value="{{ $value }}" @selected(old('treatment', $sheet->treatment) === $value)>
                                        {{ $label }}</option>@endforeach
                                    </select></div>
                                <div class="form-group col-md-6"><label for="repair_price">Valor do orçamento (R$)</label><input
                                        type="number" name="repair_price" id="repair_price" min="0" max="99999999.99"
                                        step="0.01" class="form-control"
                                        value="{{ old('repair_price', $sheet->repair_price) }}"><small
                                        class="text-muted">Obrigatório para serviços sem garantia.</small></div>
                                <div class="form-group col-md-6"><label for="payment_method">Forma de pagamento</label><input name="payment_method" id="payment_method" class="form-control"
                                        maxlength="100" value="{{ old('payment_method', $sheet->payment_method) }}"></div>
                                <div class="form-group col-md-6"><label for="changes_coating">Altera acabamento:</label><select name="changes_coating" id="changes_coating"
                                        class="form-control">
                                        <option value="0" @selected(!old('changes_coating', $sheet->changes_coating))>Não
                                        </option>
                                        <option value="1" @selected(old('changes_coating', $sheet->changes_coating))>Sim
                                        </option>
                                    </select></div>
                                <div class="form-group col-md-6"><label for="new_finish">Novo acabamento:</label><select
                                        name="new_finish" id="new_finish" class="form-control">
                                        <option value="">Não se aplica</option>
                                        @foreach (\App\Models\TechnicalSheet::FINISH_TYPES as $value => $label)
                                        <option value="{{ $value }}" @selected(old('new_finish', $sheet->new_finish) === $value)>{{ $label }}</option>@endforeach
                                    </select></div>
                                <div class="form-group col-md-6"><label for="changes_color">Altera cor:</label><select name="changes_color" id="changes_color"
                                        class="form-control">
                                        <option value="0" @selected(!old('changes_color', $sheet->changes_color))>Não</option>
                                        <option value="1" @selected(old('changes_color', $sheet->changes_color))>Sim</option>
                                    </select></div>
                                <div class="form-group col-md-6"><label for="new_color">Nova cor:</label><select
                                        name="new_color" id="new_color" class="form-control">
                                        <option value="">Não se aplica</option>
                                        @foreach (\App\Models\TechnicalSheet::COLORS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('new_color', $sheet->new_color) === $value)>{{ $label }}</option>@endforeach
                                    </select></div>
                            </div>
                            <div class="form-group"><label for="triage_notes">Orientação ao cliente /
                                    observações</label><textarea name="notes" id="triage_notes" rows="2" maxlength="5000"
                                    class="form-control">{{ old('notes') }}</textarea></div>
                            <button class="btn btn-primary">Concluir triagem e encaminhar</button>
                        </form>
                    @else
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Diagnóstico</dt>
                            <dd class="col-sm-8" style="white-space:pre-wrap">{{ $sheet->diagnosis ?: 'Ainda não registrado' }}
                            </dd>
                            <dt class="col-sm-4">Garantia</dt>
                            <dd class="col-sm-8">
                                {{ $sheet->is_guarantee === null ? 'A avaliar' : ($sheet->is_guarantee ? 'Com garantia' : 'Sem garantia') }}
                            </dd>
                            <dt class="col-sm-4">Tratamento</dt>
                            <dd class="col-sm-8">{{ \App\Models\TechnicalSheet::TREATMENTS[$sheet->treatment] ?? 'A definir' }}
                            </dd>
                            <dt class="col-sm-4">Orçamento</dt>
                            <dd class="col-sm-8">
                                {{ $sheet->repair_price !== null ? 'R$ ' . number_format((float) $sheet->repair_price, 2, ',', '.') : 'Não se aplica / a definir' }}
                            </dd>
                            <dt class="col-sm-4">Pagamento</dt>
                            <dd class="col-sm-8">{{ $sheet->payment_method ?: 'Não informado' }}</dd>
                            <dt class="col-sm-4">Altera acabamento</dt>
                            <dd class="col-sm-8">
                                @if (!$sheet->changes_coating)
                                    Não
                                @elseif ($sheet->new_finish)
                                    Sim — {{ \App\Models\TechnicalSheet::FINISH_TYPES[$sheet->new_finish] }}
                                @else
                                    Sim — a definir
                                @endif
                            </dd>
                            <dt class="col-sm-4">Altera cor</dt>
                            <dd class="col-sm-8">
                                @if (!$sheet->changes_color)
                                    Não
                                @elseif ($sheet->new_color)
                                    Sim — {{ \App\Models\TechnicalSheet::COLORS[$sheet->new_color] }}
                                @else
                                    Sim — a definir
                                @endif
                            </dd>
                        </dl>
                    @endif
                </div>
            </div>
            @if (auth()->user()->isTechnical() && $item->serviceOrder->status !== 'cancelled' && count($sheet->availableActions()))
                <div class="card shadow-sm border-primary">
                    <div class="card-header">
                        <h3 class="card-title">Próxima ação</h3>
                    </div>
                    <div class="card-body">
                        @if ($sheet->status === 'credit_pending')
                            <p class="text-muted">Confirme somente após gerar o crédito no financeiro. Informe a referência nas
                        observações.</p>@endif
                        @if ($sheet->status === 'return_pending')
                            <p class="text-muted">Esta peça não seguirá para serviço ou inspeção. Registre o motivo e finalize a devolução, ou reabra somente a triagem para corrigir a decisão.</p>
                        @endif
                        @if ($sheet->treatment === 'exchange' && $sheet->status === 'ready_for_service')
                            <p class="text-muted">Confirme após separar a peça de reposição. Registre o código e a referência do
                        estoque. Se não houver estoque, reabra a triagem para escolher outra solução.</p>@endif
                        <form method="POST" action="{{ route('technical-sheets.update', $item) }}">
                            @csrf @method('PATCH')<input type="hidden" name="version" value="{{ $sheet->version }}">
                            <div class="form-group"><label for="action">Ação</label><select name="action" id="action"
                                    class="form-control"
                                    required>@foreach ($sheet->availableActions() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('action') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select></div>
                            <div class="form-group"><label for="action_notes">Confirmação, referência ou motivo</label><textarea
                                    name="notes" id="action_notes" rows="3" maxlength="5000"
                                    class="form-control">{{ old('notes') }}</textarea><small class="text-muted">Obrigatório para
                                    aprovação/recusa do cliente, retrabalho, troca, crédito e entrega.</small></div>
                            <button class="btn btn-primary">Registrar ação</button>
                        </form>
                    </div>
                </div>
            @endif
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history mr-2"></i>Histórico da ficha</h3>
                </div>
                <div class="card-body">
                    @forelse ($sheet->exists ? $sheet->events : [] as $event)
                        <div class="border-bottom pb-3 mb-3">
                            <strong>{{ \App\Models\TechnicalSheet::STATUSES[$event->from_status] ?? $event->from_status }} →
                                {{ \App\Models\TechnicalSheet::STATUSES[$event->to_status] ?? $event->to_status }}</strong><small
                                class="d-block text-muted">{{ $event->created_at->format('d/m/Y H:i') }} ·
                                {{ $event->user->name ?? 'Usuário removido' }}</small>@if ($event->notes)
                                <p class="mb-0 mt-2" style="white-space:pre-wrap">{{ $event->notes }}</p>@endif
                        </div>
                    @empty<p class="text-muted mb-0">Nenhuma movimentação registrada. Os dados recebidos na OS já estão
                    disponíveis nesta ficha.</p>@endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
