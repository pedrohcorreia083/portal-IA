@extends('layouts.app')

@section('title', 'Visão geral')

@section('content-header')
    <div class="eyebrow">Painel operacional</div>
    <h1 class="page-title">Visão geral</h1>
    <p class="page-subtitle">
        Acompanhamento das ordens de serviço, garantias e faturamento.
        <span class="text-muted">· {{ now()->format('d/m/Y') }}</span>
    </p>
@endsection

@section('content')
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
    <div class="stat-grid">
        <div class="stat-card">
            <span class="stat-label">Ordens de serviço</span>
            <div class="stat-value">{{ number_format($stats['total_os'], 0, ',', '.') }}</div>
            <div class="stat-foot">
                @if ($trend)
                    <span class="stat-delta {{ $trend['up'] ? 'up' : 'down' }}">
                        <i class="fas fa-arrow-{{ $trend['up'] ? 'up' : 'down' }}"></i>
                        {{ abs($trend['value']) }}%
                    </span>
                    <span>vs. mês anterior</span>
                @else
                    <span>Base total do sistema</span>
                @endif
            </div>
            <i class="fas fa-file-invoice stat-icon"></i>
        </div>

        <div class="stat-card" style="--stat-tint: rgba(184,137,74,.08)">
            <span class="stat-label">Aguardando triagem</span>
            <div class="stat-value">{{ number_format($stats['os_pending'], 0, ',', '.') }}</div>
            <div class="stat-foot">
                <a href="{{ route('service-orders.index', ['status' => 'created']) }}">Ver pendentes</a>
            </div>
            <i class="fas fa-hourglass-half stat-icon"></i>
        </div>

        <div class="stat-card" style="--stat-tint: rgba(79,122,91,.08)">
            <span class="stat-label">Concluídas no mês</span>
            <div class="stat-value">{{ number_format($stats['os_completed_month'], 0, ',', '.') }}</div>
            <div class="stat-foot">
                <a href="{{ route('service-orders.index', ['status' => 'completed']) }}">Ver concluídas</a>
            </div>
            <i class="fas fa-check-double stat-icon"></i>
        </div>

        <div class="stat-card" style="--stat-tint: rgba(107,124,140,.08)">
            <span class="stat-label">Taxa de garantia</span>
            <div class="stat-value">{{ $stats['warranty_rate'] }}<small>%</small></div>
            <div class="stat-foot">
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
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Volumetria de ordens · últimos 12 meses</h3>
                    <span class="badge badge-light">Linha temporal</span>
                </div>
                <div class="card-body" style="height:320px">
                    <canvas id="osByMonthChart" aria-label="Volumetria de ordens por mês" role="img"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
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
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Composição dos itens</h3>
                    <span class="badge badge-light">Garantia vs. faturamento</span>
                </div>
                <div class="card-body" style="height:270px">
                    <canvas id="warrantyChart" aria-label="Composição financeira dos itens" role="img"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Distribuição</h3>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    @foreach ($composition as $row)
                        @php $pct = round(($row['value'] / $totalItems) * 100, 1); @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-sm text-muted">{{ $row['label'] }}</span>
                                <span class="text-sm font-weight-600" style="color: var({{ $row['token'] }})">
                                    {{ number_format($row['value'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $pct }}%; background: var({{ $row['token'] }})"
                                     aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted" style="font-size:10px">{{ $pct }}% do total</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ============ RECENTES + CONSULTORAS ============ --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Últimas ordens abertas</h3>
                    <a href="{{ route('service-orders.index') }}" class="btn btn-sm btn-outline-secondary">
                        Ver todas <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>OS</th>
                                    <th>Cliente</th>
                                    <th>Origem</th>
                                    <th>Peças</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOs as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('service-orders.show', $order) }}" class="font-weight-600">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>{{ $order->client_name }}</td>
                                        <td class="text-muted">{{ $order->origin }}</td>
                                        <td>{{ $order->items_count }}</td>
                                        <td>
                                            <span class="badge {{ $order->status_badge_class }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <strong>Nenhuma ordem registrada</strong>
                                                <p>As ordens de serviço criadas aparecerão aqui.</p>
                                                <a href="{{ route('service-orders.create') }}" class="btn btn-primary btn-sm">
                                                    Criar primeira OS
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Consultoras com mais OS</h3>
                </div>
                <div class="card-body">
                    @php $maxConsultant = $topConsultants->max('total_os') ?: 1; @endphp

                    @forelse ($topConsultants as $consultant)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-sm">{{ $consultant->consultant_name }}</span>
                                <span class="text-sm text-muted">{{ $consultant->total_os }}</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar"
                                     style="width: {{ round(($consultant->total_os / $maxConsultant) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-sm mb-0">Sem dados suficientes.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>


{{-- LINHA 4 — Eficiência por filial ─────────────────────────────────── --}}
@php
    // Meta de SLA em dias — ajuste conforme a operação
    $slaMeta    = 7;
    $slaAtencao = 14;

    $slaFor = function ($days) use ($slaMeta, $slaAtencao) {
        if ($days <= $slaMeta)    return ['class' => 'sla-good', 'label' => 'Ótimo',   'color' => '#4F7A5B']; // var(--brand-success)
        if ($days <= $slaAtencao) return ['class' => 'sla-warn', 'label' => 'Atenção', 'color' => '#B8894A']; // var(--brand-warning)
        return ['class' => 'sla-bad', 'label' => 'Crítico', 'color' => '#B45353']; // var(--brand-danger)
    };

    $overallSla = $slaFor($avgDaysOverall);
@endphp

<div class="row">
    {{-- KPI + Insights --}}
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="status-dot mr-2" style="background: var(--brand-taupe)"></span>
                    Tempo médio de atendimento
                </h3>
                <span class="badge badge-light">
                    últimos {{ $periodDays }} dias
                </span>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-baseline mb-1">
                    <span class="stat-value" style="color: {{ $overallSla['color'] }}; font-size: 42px; font-family: var(--font-display); letter-spacing: -1px; line-height: 1;">
                        {{ number_format($avgDaysOverall, 1, ',', '.') }}
                    </span>
                    <span class="ml-2 text-muted font-weight-bold" style="font-size: 14px;">dias</span>
                </div>
                <div class="mb-3">
                    <span class="branch-sla {{ $overallSla['class'] }}">{{ $overallSla['label'] }}</span>
                    <small class="text-muted ml-2">meta: ≤ {{ $slaMeta }} dias</small>
                </div>

                <div class="mt-auto">
                    @if ($fastest)
                        <div class="d-flex justify-content-between align-items-center py-3" style="border-top: 1px solid var(--line-soft);">
                            <small class="text-muted">
                                <i class="fas fa-trophy text-warning mr-2"></i> Mais rápida
                            </small>
                            <div class="text-right">
                                <div class="branch-name">{{ $fastest->origin }}</div>
                                <small class="branch-meta">{{ number_format($fastest->avg_days, 1, ',', '.') }} dias</small>
                            </div>
                        </div>
                    @endif
                    @if ($slowest && $slowest->origin !== $fastest->origin)
                        <div class="d-flex justify-content-between align-items-center py-3" style="border-top: 1px solid var(--line-soft);">
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle text-danger mr-2"></i> Atenção
                            </small>
                            <div class="text-right">
                                <div class="branch-name">{{ $slowest->origin }}</div>
                                <small class="branch-meta">{{ number_format($slowest->avg_days, 1, ',', '.') }} dias</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Gráfico por filial + ranking --}}
    <div class="col-lg-8 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="status-dot mr-2" style="background: var(--brand-dark)"></span>
                    Tempo médio por filial
                </h3>
                <div>
                    <span class="branch-sla sla-good mr-1">≤ {{ $slaMeta }}d</span>
                    <span class="branch-sla sla-warn mr-1">{{ $slaMeta + 1 }}–{{ $slaAtencao }}d</span>
                    <span class="branch-sla sla-bad">> {{ $slaAtencao }}d</span>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 240px; position: relative;">
                    <canvas id="avgDaysByBranchChart"></canvas>
                </div>

{{-- Ranking compacto --}}
<div class="mt-4">
    @forelse ($avgDaysByBranch as $row)
        @php $sla = $slaFor($row->avg_days); @endphp
        {{-- 👇 Adicionado o border-0 na última linha --}}
        <div class="d-flex align-items-center justify-content-between py-3 {{ $loop->last ? 'border-0' : '' }}" style="{{ $loop->last ? '' : 'border-bottom: 1px solid var(--line-soft);' }}">
            <div class="d-flex align-items-center">
                                <div class="branch-rank mr-3 {{ $loop->first ? 'rank-1' : '' }}">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <div class="branch-name">{{ $row->origin }}</div>
                                    <div class="branch-meta">
                                        {{ $row->total_completed }} atendimentos ·
                                        min {{ $row->min_days }}d · máx {{ $row->max_days }}d
                                    </div>
                                </div>
                            </div>
                            <div class="text-right ml-3">
                                <div class="branch-avg" style="color: {{ $sla['color'] }}">
                                    {{ number_format($row->avg_days, 1, ',', '.') }}d
                                </div>
                                <span class="branch-sla {{ $sla['class'] }}">{{ $sla['label'] }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3 mb-0">
                            Nenhum atendimento concluído nos últimos {{ $periodDays }} dias.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Chart) return;

            // As cores vêm das variáveis CSS, então acompanham a marca configurada.
            const token = (name, fallback) => {
                const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
                return value || fallback;
            };

            const palette = {
                dark: token('--brand-dark', '#3D3935'),
                taupe: token('--brand-taupe', '#857874'),
                sand: token('--brand-sand', '#C1B2A2'),
                success: token('--brand-success', '#4F7A5B'),
                warning: token('--brand-warning', '#B8894A'),
                info: token('--brand-info', '#6B7C8C'),
                grid: token('--line-soft', '#EFEAE5'),
                ink: token('--ink-mute', '#A69C96')
            };

            Chart.defaults.font.family = "'Montserrat', system-ui, sans-serif";
            Chart.defaults.font.size = 11;
            Chart.defaults.color = palette.ink;

            const monthCanvas = document.getElementById('osByMonthChart');
            if (monthCanvas) {
                const gradient = monthCanvas.getContext('2d').createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(133,120,116,.22)');
                gradient.addColorStop(1, 'rgba(133,120,116,0)');

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
                            tension: .38,
                            borderWidth: 2.5,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: palette.dark,
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, border: { display: false } },
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
                        labels: ['Showroom SP', 'Showroom Limeira', 'On-line', 'E-commerce', 'Eventos'],
                        datasets: [{
                            data: [
                                {{ $origins['showroom_sp'] ?? 0 }},
                                {{ $origins['limeira'] ?? 0 }},
                                {{ $origins['online'] ?? 0 }},
                                {{ $origins['ecommerce'] ?? 0 }},
                                {{ $origins['events'] ?? 0 }}
                            ],
                            backgroundColor: [
                                palette.dark, palette.taupe, palette.sand,
                                palette.info, palette.warning
                            ],
                            borderWidth: 3,
                            borderColor: '#fff',
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
                        labels: ['Em garantia', 'Faturado', 'Aguardando análise'],
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
                            x: { grid: { display: false }, border: { display: false } },
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
        
                        /* ─── 4. Tempo médio por filial (barras horizontais com SLA) ─── */
            const ctxAvgBranch = document.getElementById('avgDaysByBranchChart');
            if (ctxAvgBranch) {
                @php
                    $branchLabels = $avgDaysByBranch->pluck('origin');
                    $branchValues = $avgDaysByBranch->pluck('avg_days')->map(fn ($v) => round($v, 1));
                    $branchColors = $avgDaysByBranch->map(fn ($r) => $slaFor($r->avg_days)['color']);
                @endphp

                new Chart(ctxAvgBranch, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($branchLabels) !!},
                        datasets: [{
                            data: {!! json_encode($branchValues) !!},
                            backgroundColor: {!! json_encode($branchColors) !!},
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
                                    label: (ctx) => ` ${ctx.parsed.x} dias em média`
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: palette.grid },
                                border: { display: false },
                                ticks: { callback: (v) => v + 'd' }
                            },
                            y: { 
                                display: false // 👈 Oculta o nome da filial no eixo Y
                            }
                        }
                    }
                });
            }

        });
    </script>
@endpush
