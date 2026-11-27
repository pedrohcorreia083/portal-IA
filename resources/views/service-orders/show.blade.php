@extends('layouts.app')

@section('title', 'OS: ' . $serviceOrder->order_number)

@section('content-header')
    <div class="eyebrow">Ordem de serviço</div>
    <h1 class="page-title">#{{ $serviceOrder->order_number }}</h1>
    <p class="page-subtitle">
        {{ $serviceOrder->client_name }} · {{ $serviceOrder->origin }} ·
        {{ $serviceOrder->order_date->format('d/m/Y') }}
    </p>
@endsection

@section('page-actions')
    <span class="badge {{ $serviceOrder->status_badge_class }}">{{ $serviceOrder->status_label }}</span>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('service-orders.index') }}">Ordens de Serviço</a></li>
    <li class="breadcrumb-item active">{{ $serviceOrder->order_number }}</li>
@endsection

@section('content')
    @php
        $items = $serviceOrder->items;
        $statusGroups = $items->groupBy(fn ($i) => $i->technicalSheet?->status_label ?? 'Aguardando triagem');
    @endphp

    <style>
        #itemsTable th.sortable { cursor: pointer; user-select: none; white-space: nowrap; }
        #itemsTable th.sortable:hover { background-color: #f4f6f9; }
        #itemsTable th.sort-asc::after  { content: '▲'; font-size: .7em; margin-left: 4px; color: #6c757d; }
        #itemsTable th.sort-desc::after { content: '▼'; font-size: .7em; margin-left: 4px; color: #6c757d; }
        #itemsTable tbody tr { transition: background-color .15s; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    </style>

    {{-- Resumo por status --}}
    <div class="row mb-3">
        <div class="col-6 col-md-3 mb-2">
            <div class="card shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="text-xs text-uppercase text-muted font-weight-bold">Total de peças</div>
                    <div class="h4 mb-0">{{ $items->count() }}</div>
                </div>
            </div>
        </div>
        @foreach ($statusGroups as $status => $group)
            @php $color = $group->first()->technicalSheet?->status_color ?? 'secondary'; @endphp
            <div class="col-6 col-md-3 mb-2">
                <div class="card shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-xs text-uppercase text-muted font-weight-bold d-flex align-items-center">
                            <span class="status-dot bg-{{ $color }} mr-2"></span>
                            {{ $status }}
                        </div>
                        <div class="h4 mb-0">{{ $group->count() }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h3 class="card-title mb-0">
                            Peças vinculadas
                            <span class="badge badge-light">{{ $items->count() }}</span>
                        </h3>

                        {{-- Ações em massa (só para OS concluída) --}}
                        @if ($serviceOrder->status === 'completed')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Alterar Status em Massa
                                    <span class="badge badge-light ml-1" id="selectedCount">0</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <form action="{{ route('service-orders.items.update-status', $serviceOrder->id) }}"
                                          method="POST" data-bulk-form>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="send_store">
                                        <button type="submit" class="dropdown-item" data-bulk-action disabled
                                                onclick="return confirm('Enviar as peças selecionadas para a loja?')">
                                            Enviado para loja
                                        </button>
                                    </form>

                                    <div class="dropdown-divider"></div>

                                    <form action="{{ route('service-orders.items.update-status', $serviceOrder->id) }}"
                                          method="POST" data-bulk-form>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="deliver">
                                        <button type="submit" class="dropdown-item text-success" data-bulk-action disabled
                                                onclick="return confirm('Marcar as peças selecionadas como ENTREGUES?')">
                                            Entregue ao cliente
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Filtros --}}
                    <div class="form-row mt-3">
                        <div class="col-md-5 mb-2">
                            <input id="itemsSearch" type="search" class="form-control form-control-sm"
                                   placeholder="Buscar por peça, código ou acabamento...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select id="itemsStatusFilter" class="form-control form-control-sm">
                                <option value="">Todos os status</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2 d-flex justify-content-md-end align-items-center">
                            <span class="text-muted small mr-2">
                                <span id="visibleCount">{{ $items->count() }}</span> de
                                <span id="totalCount">{{ $items->count() }}</span> peças
                            </span>
                            <button type="button" id="clearFilters" class="btn btn-sm btn-light">Limpar</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:36px" class="text-center">
                                    <input type="checkbox" id="selectAllItems" title="Selecionar todas visíveis">
                                </th>
                                <th data-sort="name" class="sortable">Peça</th>
                                <th data-sort="coating" class="sortable">Acabamento recebido</th>
                                <th data-sort="status" class="sortable">Status da peça</th>
                                <th class="text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php
                                    $name        = $item->product->name ?? $item->identified_product_name ?? 'Peça sem nome';
                                    $code        = $item->product->code ?? $item->ai_identified_code ?? '';
                                    $statusLabel = $item->technicalSheet?->status_label ?? 'Aguardando triagem';
                                    $statusColor = $item->technicalSheet?->status_color ?? 'secondary';
                                    $coating     = $item->coating_name ?? 'Não informado';
                                    $search      = strtolower($name . ' ' . $code . ' ' . $coating);
                                @endphp
                                <tr data-item-id="{{ $item->id }}"
                                    data-name="{{ $name }}"
                                    data-code="{{ $code }}"
                                    data-coating="{{ $coating }}"
                                    data-status="{{ $statusLabel }}"
                                    data-search="{{ $search }}">
                                    <td class="text-center">
                                        <input type="checkbox" class="item-checkbox" value="{{ $item->id }}">
                                    </td>
                                    <td>
                                        <strong>{{ $name }}</strong>
                                        <small class="d-block text-muted">
                                            #{{ $item->id }} · {{ $code ?: 'Sem código' }}
                                        </small>
                                    </td>
                                    <td>{{ $coating }}</td>
                                    <td>
                                        <span class="badge badge-{{ $statusColor }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="text-right">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('technical-sheets.show', $item) }}">
                                            Ficha técnica
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Nenhuma peça vinculada.</td>
                                </tr>
                            @endforelse
                            <tr id="noFilterResults" style="display:none;">
                                <td colspan="5" class="text-center text-muted py-4">
                                    Nenhuma peça encontrada com os filtros atuais.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <strong>Consultor(a):</strong> {{ $serviceOrder->consultant_name }}<br>
                    <strong>Observações da OS:</strong>
                    <p class="mb-0" style="white-space:pre-wrap">
                        {{ $serviceOrder->observations ?: 'Não informadas' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Ações da OS</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('technical-sheets.index', ['order_number' => $serviceOrder->order_number]) }}"
                       class="btn btn-primary btn-block">Fichas desta OS</a>
                    <a href="{{ route('service-orders.pdf', $serviceOrder) }}" target="_blank" rel="noopener"
                       class="btn btn-dark btn-block">Visualizar PDF</a>
                    <a href="{{ route('service-orders.download-pdf', $serviceOrder) }}"
                       class="btn btn-outline-success btn-block">Baixar PDF</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('service-orders.edit', $serviceOrder) }}"
                           class="btn btn-outline-secondary btn-block">Editar dados da OS</a>
                        @if ($items->contains(fn ($item) => $item->technicalSheet !== null))
                            <p class="small text-muted my-2">A exclusão fica bloqueada quando há histórico técnico.</p>
                        @else
                            <form method="POST" action="{{ route('service-orders.destroy', $serviceOrder) }}" class="my-2"
                                  data-confirm="Excluir a OS {{ $serviceOrder->order_number }}, suas peças e as fotos vinculadas? Esta ação é permanente."
                                  onsubmit="return confirm(this.dataset.confirm)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-block">
                                    <i class="fas fa-trash mr-1" aria-hidden="true"></i> Excluir OS
                                </button>
                            </form>
                        @endif
                    @endif
                    <a href="{{ route('service-orders.index') }}" class="btn btn-light btn-block">Voltar para lista</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('itemsTable');
    if (!table) return;

    const tbody            = table.tBodies[0];
    const rows             = Array.from(tbody.querySelectorAll('tr[data-item-id]'));
    const searchInput      = document.getElementById('itemsSearch');
    const statusFilter     = document.getElementById('itemsStatusFilter');
    const clearBtn         = document.getElementById('clearFilters');
    const selectAll        = document.getElementById('selectAllItems');
    const selectedCountEl  = document.getElementById('selectedCount');
    const visibleCountEl   = document.getElementById('visibleCount');
    const totalCountEl     = document.getElementById('totalCount');
    const emptyFilterRow   = document.getElementById('noFilterResults');
    const cardEl           = table.closest('.card');
    const bulkButtons      = cardEl.querySelectorAll('[data-bulk-action]');
    const bulkForms        = cardEl.querySelectorAll('form[data-bulk-form]');

    if (totalCountEl) totalCountEl.textContent = rows.length;

    // Popula o select de status com os valores únicos da tabela
    Array.from(new Set(rows.map(r => r.dataset.status).filter(Boolean))).sort()
        .forEach(s => {
            const opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s;
            statusFilter.appendChild(opt);
        });

    function updateSelectionState() {
        const visibleRows = rows.filter(r => r.style.display !== 'none');
        const checkedRows = visibleRows.filter(r => r.querySelector('.item-checkbox')?.checked);
        const selected    = checkedRows.length;

        if (selectedCountEl) selectedCountEl.textContent = selected;
        bulkButtons.forEach(btn => { btn.disabled = selected === 0; });

        if (selectAll) {
            selectAll.checked       = visibleRows.length > 0 && checkedRows.length === visibleRows.length;
            selectAll.indeterminate = checkedRows.length > 0 && checkedRows.length < visibleRows.length;
        }
    }

    function applyFilters() {
        const term   = (searchInput.value || '').toLowerCase().trim();
        const status = statusFilter.value;
        let visible  = 0;

        rows.forEach(row => {
            const matchText   = !term   || (row.dataset.search || '').includes(term);
            const matchStatus = !status || row.dataset.status === status;
            const show        = matchText && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (visibleCountEl) visibleCountEl.textContent = visible;
        if (emptyFilterRow) {
            emptyFilterRow.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
        }
        updateSelectionState();
    }

    searchInput?.addEventListener('input', applyFilters);
    statusFilter?.addEventListener('change', applyFilters);
    clearBtn?.addEventListener('click', function () {
        searchInput.value = '';
        statusFilter.value = '';
        applyFilters();
    });

    selectAll?.addEventListener('change', function () {
        rows.forEach(r => {
            if (r.style.display !== 'none') {
                const cb = r.querySelector('.item-checkbox');
                if (cb) cb.checked = selectAll.checked;
            }
        });
        updateSelectionState();
    });

    tbody.addEventListener('change', function (e) {
        if (e.target.classList.contains('item-checkbox')) updateSelectionState();
    });

    // Ordenação
    table.querySelectorAll('th[data-sort]').forEach(th => {
        th.addEventListener('click', function () {
            const key     = th.dataset.sort;
            const nextDir = th.dataset.dir === 'asc' ? 'desc' : 'asc';

            table.querySelectorAll('th[data-sort]').forEach(h => {
                h.dataset.dir = '';
                h.classList.remove('sort-asc', 'sort-desc');
            });
            th.dataset.dir = nextDir;
            th.classList.add('sort-' + nextDir);

            const sorted = [...rows].sort((a, b) => {
                const av = (a.dataset[key] || '').toLowerCase();
                const bv = (b.dataset[key] || '').toLowerCase();
                return nextDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
            });

            sorted.forEach(r => tbody.appendChild(r));
            if (emptyFilterRow) tbody.appendChild(emptyFilterRow);
        });
    });

    // Ações em massa: injeta item_ids[] antes do envio
    bulkForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            form.querySelectorAll('input[name="item_ids[]"]').forEach(el => el.remove());

            const ids = rows
                .filter(r => r.querySelector('.item-checkbox')?.checked)
                .map(r => r.dataset.itemId);

            if (ids.length === 0) {
                e.preventDefault();
                alert('Selecione pelo menos uma peça.');
                return;
            }

            ids.forEach(id => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'item_ids[]';
                input.value = id;
                form.appendChild(input);
            });
        });
    });

    applyFilters();
});
</script>
@endpush
