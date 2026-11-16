@extends('layouts.app')

@section('title', 'Ordens de Serviço')

@section('content-header')
    <div>
        <h1 class="m-0">Ordens de Serviço</h1>
        <small class="text-muted">Gerencie todas as ordens de serviço</small>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Ordens de Serviço</li>
@endsection

@php
    $currentSort = request('sort', 'created_at');
    $currentDir = request('direction', 'desc');

    $sortUrl = function ($field) use ($currentSort, $currentDir) {
        $dir = ($currentSort === $field && $currentDir === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $field, 'direction' => $dir, 'page' => 1]);
    };

    $sortIcon = function ($field) use ($currentSort, $currentDir) {
        if ($currentSort !== $field) {
            return '<i class="fas fa-sort text-muted ml-1" style="opacity:.4"></i>';
        }
        return $currentDir === 'asc'
            ? '<i class="fas fa-sort-up ml-1"></i>'
            : '<i class="fas fa-sort-down ml-1"></i>';
    };

    // Filtros aplicados, para exibir os "badges" removíveis
    $activeFilters = collect([
        'order_number' => ['label' => 'Nº OS', 'icon' => 'fa-hashtag'],
        'client_name' => ['label' => 'Cliente', 'icon' => 'fa-user'],
        'consultant_name' => ['label' => 'Consultor', 'icon' => 'fa-user-tie'],
        'origin' => ['label' => 'Origem', 'icon' => 'fa-store'],
        'status' => ['label' => 'Status', 'icon' => 'fa-flag'],
        'date_from' => ['label' => 'De', 'icon' => 'fa-calendar-alt'],
        'date_to' => ['label' => 'Até', 'icon' => 'fa-calendar-alt'],
    ])->filter(fn($meta, $key) => request()->filled($key));

    $canBulkDelete = \Illuminate\Support\Facades\Route::has('service-orders.bulk-destroy');
@endphp

@section('content')

    {{-- AÇÕES SUPERIORES --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto">
            <a href="{{ route('service-orders.export', request()->all()) }}" class="btn btn-success shadow-sm">
                <i class="fas fa-file-excel mr-1"></i>
                Exportar Excel
            </a>
            <a href="{{ route('service-orders.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus mr-1"></i>
                Nova OS
            </a>
        </div>
    </div>

    {{-- FILTROS ATIVOS --}}
    @if ($activeFilters->isNotEmpty())
        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
            <small class="text-muted mr-1">Filtros ativos:</small>
            @foreach ($activeFilters as $key => $meta)
                @php
                    $without = request()->except($key);
                    $removeUrl = url()->current() . ($without ? '?' . http_build_query($without) : '');
                @endphp
                <a href="{{ $removeUrl }}" class="badge badge-primary px-3 py-2 text-decoration-none">
                    <i class="fas {{ $meta['icon'] }} mr-1"></i>
                    {{ $meta['label'] }}: <strong>{{ request($key) }}</strong>
                    <i class="fas fa-times ml-1"></i>
                </a>
            @endforeach
            <a href="{{ route('service-orders.index') }}" class="btn btn-sm btn-link text-danger p-0 ml-2">
                Limpar todos
            </a>
        </div>
    @endif

    {{-- FILTROS --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold m-0">
                <i class="fas fa-filter mr-2"></i>
                Filtros
            </h3>
            <button type="button" class="btn btn-sm btn-link p-0" data-toggle="collapse" data-target="#advancedFilters"
                aria-expanded="true">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>

        <div class="collapse show" id="advancedFilters">
            <div class="card-body">
                <form method="GET" action="{{ route('service-orders.index') }}">
                    {{-- Preserva ordenação atual ao filtrar --}}
                    <input type="hidden" name="sort" value="{{ $currentSort }}">
                    <input type="hidden" name="direction" value="{{ $currentDir }}">

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Nº OS</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                </div>
                                <input type="text" name="order_number" class="form-control" placeholder="Digite o número"
                                    value="{{ request('order_number') }}">
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Cliente</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" name="client_name" class="form-control" placeholder="Nome do cliente"
                                    value="{{ request('client_name') }}">
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Consultor(a)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                </div>
                                <input type="text" name="consultant_name" class="form-control" placeholder="Consultor"
                                    value="{{ request('consultant_name') }}">
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Origem</label>
                            <select name="origin" class="form-control">
                                <option value="">Todas</option>
                                @foreach (['SHOWROOM SP', 'SHOWROOM LIMEIRA', 'SHOWROOM ON-LINE', 'E-COMMERCE', 'EVENTOS'] as $origin)
                                    <option value="{{ $origin }}" {{ request('origin') === $origin ? 'selected' : '' }}>
                                        {{ $origin }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Status</label>
                            {{-- Ajuste os valores abaixo conforme os status reais da sua app --}}
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                @foreach ([
                                        'pending' => 'Pendente',
                                        'in_progress' => 'Em andamento',
                                        'completed' => 'Concluída',
                                        'cancelled' => 'Cancelada',
                                    ] as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Data inicial</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Data final</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Itens por página</label>
                            <select name="per_page" class="form-control" onchange="this.form.submit()">
                                @foreach ([10, 25, 50, 100] as $pp)
                                    <option value="{{ $pp }}" {{ (int) request('per_page', 10) === $pp ? 'selected' : '' }}>
                                        {{ $pp }} por página
                                    </option>
                                @endforeach
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
    </div>

    {{-- LISTAGEM --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title m-0 font-weight-bold">
                    Lista de Ordens de Serviço
                </h3>

                <div class="d-flex align-items-center gap-2">
                    @if ($canBulkDelete)
                        <form id="bulkActionForm" method="POST" action="{{ route('service-orders.bulk-destroy') }}"
                            class="d-inline-flex align-items-center">
                            @csrf
                            @method('DELETE')
                            <span class="badge badge-light border mr-2">
                                <span id="bulkSelectedCount">0</span> selecionadas
                            </span>
                            <button type="submit" class="btn btn-sm btn-danger" data-bulk-action disabled
                                onclick="return confirm('Excluir as ordens selecionadas?')">
                                <i class="fas fa-trash mr-1"></i> Excluir
                            </button>
                        </form>
                    @endif

                    <span class="badge badge-secondary px-3 py-2">
                        {{ $serviceOrders->total() }} {{ $serviceOrders->total() === 1 ? 'registro' : 'registros' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="serviceOrdersTable">
                    <thead class="bg-light">
                        <tr>
                            <th width="36" class="text-center">
                                @if ($canBulkDelete)
                                    <input type="checkbox" id="selectAllOrders" title="Selecionar todas">
                                @endif
                            </th>
                            <th width="70">#</th>
                            <th>
                                <a href="{{ $sortUrl('order_number') }}" class="text-dark text-decoration-none">
                                    Nº OS {!! $sortIcon('order_number') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('client_name') }}" class="text-dark text-decoration-none">
                                    Cliente {!! $sortIcon('client_name') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('origin') }}" class="text-dark text-decoration-none">
                                    Origem {!! $sortIcon('origin') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('order_date') }}" class="text-dark text-decoration-none">
                                    Data {!! $sortIcon('order_date') !!}
                                </a>
                            </th>
                            <th>Itens</th>
                            <th>
                                <a href="{{ $sortUrl('status') }}" class="text-dark text-decoration-none">
                                    Status {!! $sortIcon('status') !!}
                                </a>
                            </th>
                            <th class="text-center" width="130">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($serviceOrders as $serviceOrder)
                            <tr data-order-id="{{ $serviceOrder->id }}">
                                <td class="text-center">
                                    @if ($canBulkDelete)
                                        <input type="checkbox" class="order-checkbox" value="{{ $serviceOrder->id }}">
                                    @endif
                                </td>

                                <td>
                                    <span class="text-muted font-weight-bold">
                                        {{ $loop->iteration + ($serviceOrders->currentPage() - 1) * $serviceOrders->perPage() }}
                                    </span>
                                </td>

                                <td><strong>{{ $serviceOrder->order_number }}</strong></td>

                                <td>{{ $serviceOrder->client_name }}</td>

                                <td>
                                    <span class="badge badge-info px-3 py-2">
                                        {{ $serviceOrder->origin }}
                                    </span>
                                </td>

                                <td>{{ $serviceOrder->order_date->format('d/m/Y') }}</td>

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
                                        <a href="{{ route('service-orders.show', $serviceOrder) }}" class="btn btn-info btn-sm"
                                            title="Visualizar">
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
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <div class="text-muted">
                                        @if ($activeFilters->isNotEmpty())
                                            Nenhuma ordem de serviço encontrada com os filtros aplicados.
                                            <div class="mt-3">
                                                <a href="{{ route('service-orders.index') }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-times mr-1"></i> Limpar filtros
                                                </a>
                                            </div>
                                        @else
                                            Nenhuma ordem de serviço encontrada.
                                        @endif
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
                        {{-- appends preserva filtros + ordenação + per_page --}}
                        {{ $serviceOrders->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAllOrders');
            const checkboxes = document.querySelectorAll('.order-checkbox');
            const bulkForm = document.getElementById('bulkActionForm');
            const bulkCountEl = document.getElementById('bulkSelectedCount');
            const bulkButtons = document.querySelectorAll('[data-bulk-action]');

            if (!checkboxes.length) return;

            function updateSelectionState() {
                const checked = document.querySelectorAll('.order-checkbox:checked');
                if (bulkCountEl) bulkCountEl.textContent = checked.length;
                bulkButtons.forEach(b => b.disabled = checked.length === 0);

                if (selectAll) {
                    selectAll.checked = checked.length === checkboxes.length && checkboxes.length > 0;
                    selectAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
                }
            }

            selectAll?.addEventListener('change', function (e) {
                checkboxes.forEach(c => c.checked = e.target.checked);
                updateSelectionState();
            });

            checkboxes.forEach(c => c.addEventListener('change', updateSelectionState));

            bulkForm?.addEventListener('submit', function (e) {
                const ids = Array.from(document.querySelectorAll('.order-checkbox:checked'))
                    .map(c => c.value);

                if (ids.length === 0) {
                    e.preventDefault();
                    alert('Selecione pelo menos uma ordem de serviço.');
                    return;
                }

                // Remove inputs antigos antes de injetar os atuais
                bulkForm.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    bulkForm.appendChild(input);
                });
            });
        });
    </script>
@endpush