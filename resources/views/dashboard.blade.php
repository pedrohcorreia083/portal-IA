@extends('layouts.app')

@section('title', 'Visão geral')

@push('css')
    <style>
        .dashboard-date {
            flex: 1 1 145px;
        }

        .dashboard-filter-actions .btn {
            min-height: 38px;
        }

        .dashboard-kpis {
            --stat-tint: color-mix(in srgb, var(--brand-taupe) 7%, transparent);
            grid-template-columns: minmax(0, 1fr);
        }

        .dashboard-attendance-value {
            font-family: var(--font-display);
            font-size: 42px;
            letter-spacing: -1px;
            line-height: 1;
        }

        .dashboard-attendance-row {
            border-top: 1px solid var(--line-soft);
        }

        .dashboard-sla .badge-warning {
            color: color-mix(in srgb, var(--brand-warning) 68%, var(--ink));
        }

        #osByMonthChart {
            color: transparent;
            color: color-mix(in srgb, var(--brand-taupe) 22%, transparent);
        }

        @media (min-width: 576px) {
            .dashboard-date {
                flex: 0 1 200px;
            }

            .dashboard-kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1200px) {
            .dashboard-kpis {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
@endpush

@section('content-header')
    <div class="eyebrow">Painel operacional</div>
    <h1 class="page-title">Visão geral</h1>
    <p class="page-subtitle">
        Acompanhamento das ordens de serviço, garantias e faturamento.
    </p>
@endsection

@section('content')
    <form method="GET" action="{{ route('dashboard') }}" class="card" aria-label="Período do dashboard"
          aria-describedby="dashboard-date-note">
        <div class="card-body">
            <div class="filter-bar">
                <div class="form-group dashboard-date">
                    <label for="dashboard-date-from">Data inicial</label>
                    <input type="date" id="dashboard-date-from" name="date_from" class="form-control"
                           value="{{ $dates['date_from'] }}" required>
                </div>
                <div class="form-group dashboard-date">
                    <label for="dashboard-date-to">Data final</label>
                    <input type="date" id="dashboard-date-to" name="date_to" class="form-control"
                           value="{{ $dates['date_to'] }}" required>
                </div>
                <div class="dashboard-filter-actions d-flex">
                    <button type="submit" class="btn btn-primary mr-2">Filtrar</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </div>
            <p id="dashboard-date-note" class="small text-muted mt-2 mb-0">O período considera a data de abertura da OS.</p>
        </div>
    </form>

    @if ($stats['total_os'] === 0)
        <p class="alert alert-info" role="status">Nenhuma ordem de serviço foi aberta no período.</p>
    @endif

    @php
        $warranty = $warrantyStats['warranty'] ?? 0;
        $paid = $warrantyStats['paid'] ?? 0;
        $pending = $warrantyStats['pending_analysis'] ?? 0;
        $totalItems = max($warranty + $paid + $pending, 1);

        // Tendência do último mês vs. anterior.
        $months = $osByMonth->values();
        $trend = null;
        if ($months->count() >= 2) {
            $last = (int) ($months[$months->count() - 1]['count'] ?? 0);
            $prev = (int) ($months[$months->count() - 2]['count'] ?? 0);
            if ($prev > 0) {
                $diff = (($last - $prev) / $prev) * 100;
                $trend = ['value' => round($diff, 1), 'up' => $diff >= 0];
            }
        }

        $composition = [
            ['label' => 'Em garantia', 'value' => $warranty, 'token' => '--brand-warning'],
            ['label' => 'Faturado', 'value' => $paid, 'token' => '--brand-success'],
            ['label' => 'Aguardando análise', 'value' => $pending, 'token' => '--brand-info'],
        ];
    @endphp

    {{-- ============ KPIs ============ --}}
    <div class="stat-grid dashboard-kpis">
        <div class="stat-card">
            <span class="stat-label">Ordens de serviço</span>
            <div class="stat-value">{{ number_format($stats['total_os'], 0, ',', '.') }}</div>
            <div class="stat-foot">
                @if ($trend)
                    <span id="dash-kpi-trend" class="stat-delta {{ $trend['up'] ? 'up' : 'down' }}">
                        <i class="fas fa-arrow-{{ $trend['up'] ? 'up' : 'down' }}"></i>
                        {{ abs($trend['value']) }}%
                    </span>
                    <span>vs. mês anterior</span>
                @endif
            </div>
            <i class="fas fa-file-invoice stat-icon"></i>
        </div>

        <div class="stat-card" style="--stat-tint: color-mix(in srgb, var(--brand-warning) 8%, transparent)">
            <span class="stat-label">Aguardando triagem</span>
            <div class="stat-value">{{ number_format($stats['os_pending'], 0, ',', '.') }}</div>
            <div class="stat-foot">
                <a href="{{ route('service-orders.index', $dates + ['status' => 'created']) }}">Ver pendentes</a>
            </div>
            <i class="fas fa-hourglass-half stat-icon"></i>
        </div>

        <div class="stat-card" style="--stat-tint: color-mix(in srgb, var(--brand-success) 8%, transparent)">
            <span class="stat-label">OS concluídas</span>
            <div class="stat-value">{{ number_format($stats['os_completed'], 0, ',', '.') }}</div>
            <div class="stat-foot">
                <a href="{{ route('service-orders.index', $dates + ['status' => 'completed']) }}">Ver concluídas</a>
            </div>
            <i class="fas fa-check-double stat-icon"></i>
        </div>

        <div class="stat-card" style="--stat-tint: color-mix(in srgb, var(--brand-info) 8%, transparent)">
            <span class="stat-label">Taxa de garantia</span>
            <div class="stat-value">{{ $stats['warranty_rate'] }}<small>%</small></div>
            <div class="stat-foot flex-wrap pr-3">
                <span>Ticket médio de reparo</span>
                <strong style="color:var(--ink);font-weight:600">
                    R$ {{ number_format((float) $stats['avg_ticket'], 2, ',', '.') }}
                </strong>
            </div>
            <i class="fas fa-shield-alt stat-icon"></i>
        </div>
    </div>

    {{-- ============ VOLUMETRIA + CANAIS ============ --}}
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title">Volumetria de ordens por mês</h3>
                </div>
                <div class="card-body" style="height:320px">
                    <canvas id="osByMonthChart" aria-label="Volumetria de ordens por mês" role="img"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title">Canais de origem</h3>
                </div>
                <div class="card-body" style="height:320px">
                    <canvas id="originChart" aria-label="Distribuição por canal de origem" role="img"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ COMPOSIÇÃO + DISTRIBUIÇÃO ============ --}}
    <div class="row">
        <div class="col-lg-7 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title">Composição dos itens</h3>
                </div>
                <div class="card-body" style="height:270px">
                    <canvas id="warrantyChart" aria-label="Composição financeira dos itens" role="img"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title">Distribuição</h3>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    @foreach ($composition as $row)
                        @php $pct = round(($row['value'] / $totalItems) * 100, 1); @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>{{ $row['label'] }}</span>
                                <strong>
                                    {{ number_format($row['value'], 0, ',', '.') }}
                                </strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $pct }}%; background: var({{ $row['token'] }})"
                                     aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">{{ number_format($pct, 1, ',', '.') }}% do total</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <section class="card" aria-labelledby="top-incidents-title">
        <div class="card-header">
            <h3 id="top-incidents-title" class="card-title">10 maiores incidentes</h3>
        </div>
        @if ($topIncidents->isNotEmpty())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Serviço solicitado</th>
                            <th scope="col" class="text-right">Peças</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topIncidents as $incident)
                            <tr>
                                <td class="font-weight-bold">{{ $incident['label'] }}</td>
                                <td class="text-right">{{ number_format($incident['count'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="card-body">
                <p class="text-muted mb-0">Nenhum serviço solicitado no período.</p>
            </div>
        @endif
    </section>


    @php
        $slaMeta = 7;
        $slaAtencao = 14;
        $slaFor = fn ($days) => match (true) {
            $days <= $slaMeta => ['tone' => 'success', 'label' => 'Ótimo'],
            $days <= $slaAtencao => ['tone' => 'warning', 'label' => 'Atenção'],
            default => ['tone' => 'danger', 'label' => 'Crítico'],
        };
        $overallSla = $avgDaysByBranch->isNotEmpty() ? $slaFor($avgDaysOverall) : null;
    @endphp

    <div class="row dashboard-sla">
        <div class="col-lg-4 mb-3">
            <section class="card mb-0" aria-labelledby="attendance-title">
                <div class="card-header">
                    <h3 id="attendance-title" class="card-title">Tempo médio de atendimento</h3>
                </div>
                <div class="card-body">
                    @if ($overallSla)
                        <div class="d-flex align-items-baseline mb-2">
                            <span class="dashboard-attendance-value" style="color: var(--brand-{{ $overallSla['tone'] }})">
                                {{ number_format($avgDaysOverall, 1, ',', '.') }}
                            </span>
                            <span class="ml-2 text-muted font-weight-bold">dias</span>
                        </div>
                        <div class="mb-3">
                            <span class="badge badge-{{ $overallSla['tone'] }}">{{ $overallSla['label'] }}</span>
                            <small class="text-muted ml-1">Meta de até {{ $slaMeta }} dias</small>
                        </div>
                        <dl class="mb-0">
                            <div class="dashboard-attendance-row d-flex flex-wrap justify-content-between py-2">
                                <dt class="small pr-2">Filial mais rápida</dt>
                                <dd class="mb-0 ml-auto text-right">
                                    <strong class="d-block">{{ $fastest->origin }}</strong>
                                    <small class="text-muted">{{ number_format($fastest->avg_days, 1, ',', '.') }} dias</small>
                                </dd>
                            </div>
                            @if ($slowest->origin !== $fastest->origin)
                                <div class="dashboard-attendance-row d-flex flex-wrap justify-content-between pt-2">
                                    <dt class="small pr-2">Filial mais demorada</dt>
                                    <dd class="mb-0 ml-auto text-right">
                                        <strong class="d-block">{{ $slowest->origin }}</strong>
                                        <small class="text-muted">{{ number_format($slowest->avg_days, 1, ',', '.') }} dias</small>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    @else
                        <p class="text-muted mb-0">Sem atendimentos concluídos no período.</p>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-lg-8 mb-3">
            <section class="card mb-0" aria-labelledby="branches-title">
                <div class="card-header">
                    <h3 id="branches-title" class="card-title">Tempo médio por filial</h3>
                    @if ($overallSla)
                        <div class="d-flex flex-wrap gap-2" aria-label="Faixas de prazo">
                            <span class="badge badge-success">Até {{ $slaMeta }} d</span>
                            <span class="badge badge-warning" title="Mais de {{ $slaMeta }} e até {{ $slaAtencao }} dias">Até {{ $slaAtencao }} d</span>
                            <span class="badge badge-danger">Acima de {{ $slaAtencao }} d</span>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if ($overallSla)
                        <div style="height: {{ max(160, $avgDaysByBranch->count() * 42 + 34) }}px; position: relative;">
                            <canvas id="avgDaysByBranchChart" aria-label="Tempo médio de atendimento por filial" role="img"></canvas>
                        </div>
                        <ul class="list-unstyled mt-3 mb-0">
                            @foreach ($avgDaysByBranch as $row)
                                @php $sla = $slaFor($row->avg_days); @endphp
                                <li class="dashboard-attendance-row d-flex align-items-center justify-content-between py-2">
                                    <div class="pr-2">
                                        <strong class="d-block">{{ $row->origin }}</strong>
                                        <small class="text-muted">
                                            {{ $row->total_completed }} {{ $row->total_completed === 1 ? 'atendimento' : 'atendimentos' }}
                                            · mín. {{ $row->min_days }} d · máx. {{ $row->max_days }} d
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <strong class="d-block text-nowrap">{{ number_format($row->avg_days, 1, ',', '.') }} d</strong>
                                        <span class="badge badge-{{ $sla['tone'] }}">{{ $sla['label'] }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">Nenhuma filial tem atendimentos concluídos no período.</p>
                    @endif
                </div>
            </section>
        </div>
    </div>

@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Chart) return;

            const styles = getComputedStyle(document.documentElement);
            const token = (name) => styles.getPropertyValue(name).trim();

            const palette = {
                dark: token('--brand-dark'),
                taupe: token('--brand-taupe'),
                sand: token('--brand-sand'),
                success: token('--brand-success'),
                warning: token('--brand-warning'),
                danger: token('--brand-danger'),
                info: token('--brand-info'),
                grid: token('--line-soft'),
                ink: token('--ink'),
                surface: token('--surface')
            };

            Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
            Chart.defaults.font.size = 11;
            Chart.defaults.color = palette.ink;

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                Chart.defaults.animation = false;
            }

            const monthCanvas = document.getElementById('osByMonthChart');
            if (monthCanvas) {
                const gradient = monthCanvas.getContext('2d').createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, getComputedStyle(monthCanvas).color);
                gradient.addColorStop(1, 'transparent');

                new Chart(monthCanvas, {
                    type: 'line',
                    data: {
                        labels: @json($osByMonth->pluck('month')),
                        datasets: [{
                            label: 'Ordens',
                            data: @json($osByMonth->pluck('count')),
                            borderColor: palette.taupe,
                            backgroundColor: gradient,
                            fill: true,
                            cubicInterpolationMode: 'monotone',
                            borderWidth: 2.5,
                            pointRadius: {{ $osByMonth->count() === 1 ? 4 : 0 }},
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: palette.dark,
                            pointHoverBorderColor: palette.surface,
                            pointHoverBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { maxRotation: 0 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: palette.grid },
                                border: { display: false },
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            }

            const originCanvas = document.getElementById('originChart');
            if (originCanvas) {
                new Chart(originCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: @json($origins->keys()),
                        datasets: [{
                            data: @json($origins->values()),
                            backgroundColor: [
                                palette.dark, palette.taupe, palette.sand,
                                palette.info, palette.warning
                            ],
                            borderWidth: 3,
                            borderColor: palette.surface,
                            hoverOffset: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '66%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 9, padding: 12, usePointStyle: true, pointStyle: 'circle' }
                            }
                        }
                    }
                });
            }

            const compositionCanvas = document.getElementById('warrantyChart');
            if (compositionCanvas) {
                new Chart(compositionCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Em garantia', 'Faturado', ['Aguardando', 'análise']],
                        datasets: [{
                            data: [
                                {{ $warrantyStats['warranty'] ?? 0 }},
                                {{ $warrantyStats['paid'] ?? 0 }},
                                {{ $warrantyStats['pending_analysis'] ?? 0 }}
                            ],
                            backgroundColor: [palette.warning, palette.success, palette.info],
                            borderRadius: 5,
                            maxBarThickness: 52
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { maxRotation: 0 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: palette.grid },
                                border: { display: false },
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            }

            const branchCanvas = document.getElementById('avgDaysByBranchChart');
            if (branchCanvas) {
                @php
                    $branchLabels = $avgDaysByBranch->pluck('origin');
                    $branchValues = $avgDaysByBranch->pluck('avg_days')->map(fn ($v) => round($v, 1));
                    $branchTones = $avgDaysByBranch->map(fn ($row) => $slaFor($row->avg_days)['tone']);
                @endphp

                const tones = @json($branchTones);

                new Chart(branchCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($branchLabels),
                        datasets: [{
                            data: @json($branchValues),
                            backgroundColor: tones.map(tone => palette[tone]),
                            borderRadius: 6,
                            maxBarThickness: 26
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (context) => ' ' + context.parsed.x.toLocaleString('pt-BR') + ' dias em média'
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: palette.grid },
                                border: { display: false },
                                ticks: { callback: (value) => value + ' d' }
                            },
                            y: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            }

        });
    </script>
@endpush
