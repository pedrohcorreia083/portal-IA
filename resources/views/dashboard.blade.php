@extends('layouts.app')

@section('title', 'Dashboard')

@section('content-header')
    <span class="text-dark font-weight-bold">Dashboard de Operações</span>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <style>
        :root {
            --dash-primary: #4361ee;
            --dash-success: #06d6a0;
            --dash-warning: #ffd166;
            --dash-danger:  #ef476f;
            --dash-info:    #4cc9f0;
            --dash-purple:  #7209b7;
            --dash-dark:    #1d3557;
            --dash-gray:    #8d99ae;
            --dash-border:  #f1f3f7;
        }

        /* HERO */
        .hero-strip {
            background: linear-gradient(135deg, #1d3557 0%, #4361ee 100%);
            border-radius: 16px;
            padding: 1.5rem 1.75rem;
            color: #fff;
            margin-bottom: 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .hero-strip::after {
            content: '';
            position: absolute;
            right: -40px; top: -40px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
        }
        .hero-strip h2 { font-weight: 800; font-size: 1.4rem; margin-bottom: .35rem; }
        .hero-strip p  { margin: 0; opacity: .85; font-size: .9rem; }
        .hero-chip {
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(4px);
            border-radius: 999px;
            padding: .4rem .9rem;
            font-size: .78rem;
            font-weight: 700;
            display: inline-flex; align-items: center;
            margin-left: .35rem;
        }
        .hero-chip i { margin-right: .35rem; }
        .pulse-dot { animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }

        /* KPI */
        .dash-kpi {
            border-radius: 16px;
            background: #fff;
            padding: 1.25rem 1.25rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,.04);
            transition: transform .25s ease, box-shadow .25s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        .dash-kpi:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        }
        .dash-kpi::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 4px;
            background: var(--kpi-color);
        }
        .dash-kpi-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            background: var(--kpi-color);
            box-shadow: 0 6px 14px rgba(0,0,0,.12);
            font-size: 1.15rem;
        }
        .dash-kpi-value {
            font-size: 1.9rem; font-weight: 800; line-height: 1;
            color: var(--dash-dark);
        }
        .dash-kpi-label {
            font-size: .75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em;
            color: var(--dash-gray);
        }
        .dash-kpi-trend { font-size: .78rem; font-weight: 800; }
        .dash-kpi-footer {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: .9rem;
            padding: .75rem 0;
            border-top: 1px solid var(--dash-border);
            font-weight: 700;
            font-size: .8rem;
            text-decoration: none !important;
        }

        /* CARD GENÉRICO */
        .dash-card {
            border-radius: 16px;
            border: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,.04);
            background: #fff;
            overflow: hidden;
        }
        .dash-card .card-header {
            background: #fff;
            border-bottom: 1px solid var(--dash-border);
            padding: 1rem 1.25rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .dash-card .card-title {
            font-weight: 800;
            font-size: .92rem;
            color: var(--dash-dark);
            margin: 0;
            display: flex; align-items: center;
        }
        .badge-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        /* COMPOSIÇÃO (barras percentuais) */
        .warranty-item { margin-bottom: 1.1rem; }
        .warranty-item:last-child { margin-bottom: 0; }
        .warranty-label { font-size: .85rem; font-weight: 700; color: var(--dash-dark); }
        .warranty-value { font-size: 1rem; font-weight: 800; }
        .warranty-pct   { font-size: .72rem; color: var(--dash-gray); font-weight: 700; }
        .progress-warranty {
            height: 10px;
            border-radius: 999px;
            background: #eef1f6;
            overflow: hidden;
        }
        .progress-warranty .bar {
            height: 100%;
            border-radius: 999px;
            transition: width .9s cubic-bezier(.2,.8,.2,1);
        }
    </style>

    {{-- HERO --}}
    <div class="hero-strip">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2><i class="fas fa-bolt mr-2"></i>Visão geral da operação</h2>
                <p>Resumo em tempo real das ordens de serviço, canais de origem e desempenho.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <span class="hero-chip">
                    <i class="fas fa-circle pulse-dot" style="font-size:.5rem;color:#06d6a0;"></i> Ao vivo
                </span>
                <span class="hero-chip">
                    <i class="far fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    @php
        $kpis = [
            [
                'label' => 'Total de OS',
                'value' => $stats['total_os'],
                'icon' => 'fa-file-invoice',
                'color' => 'var(--dash-primary)',
                'route' => route('service-orders.index'),
            ],
            [
                'label' => 'Pendentes',
                'value' => $stats['os_pending'],
                'icon' => 'fa-hourglass-half',
                'color' => 'var(--dash-warning)',
                'route' => route('service-orders.index', ['status' => 'created']),
            ],
            [
                'label' => 'Concluídas no mês',
                'value' => $stats['os_completed_month'],
                'icon' => 'fa-check-double',
                'color' => 'var(--dash-success)',
                'route' => route('service-orders.index', ['status' => 'completed']),
            ],
            [
                'label' => 'Taxa de garantia',
                'value' => $stats['warranty_rate'] . '%',
                'icon' => 'fa-shield-alt',
                'color' => 'var(--dash-purple)',
                'route' => '#',
            ],
        ];

        // Tendência do último mês vs. anterior (a partir de osByMonth)
        $trend = null;
        $months = $osByMonth->values();
        if ($months->count() >= 2) {
            $last = (int) ($months[$months->count() - 1]['count'] ?? 0);
            $prev = (int) ($months[$months->count() - 2]['count'] ?? 0);
            if ($prev > 0) {
                $diff = (($last - $prev) / $prev) * 100;
                $trend = ['value' => round($diff, 1), 'up' => $diff >= 0];
            }
        }
    @endphp

    <div class="row">
        @foreach ($kpis as $kpi)
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dash-kpi" style="--kpi-color: {{ $kpi['color'] }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="dash-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>

                        @if ($trend && $loop->first)
                            <span class="dash-kpi-trend {{ $trend['up'] ? 'text-success' : 'text-danger' }}">
                                <i class="fas fa-arrow-{{ $trend['up'] ? 'up' : 'down' }} mr-1"></i>
                                {{ abs($trend['value']) }}%
                            </span>
                        @endif
                    </div>

                    <div class="mt-3">
                        <div class="dash-kpi-value">{{ $kpi['value'] }}</div>
                        <div class="dash-kpi-label mt-1">{{ $kpi['label'] }}</div>
                    </div>

                    <a href="{{ $kpi['route'] }}"
                       class="dash-kpi-footer"
                       style="color: {{ $kpi['color'] }}">
                        Ver detalhes <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- LINHA 2 — Volumetria + Origem --}}
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="dash-card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge-dot" style="background: var(--dash-primary)"></span>
                        Volumetria de OS · últimos 12 meses
                    </h3>
                    <span class="badge badge-light px-3 py-2">Linha temporal</span>
                </div>
                <div class="card-body" style="height: 340px; position: relative;">
                    <canvas id="osByMonthChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="dash-card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge-dot" style="background: var(--dash-purple)"></span>
                        Canais de origem
                    </h3>
                </div>
                <div class="card-body" style="height: 340px; position: relative;">
                    <canvas id="originChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- LINHA 3 — Composição financeira + barras percentuais --}}
    <div class="row">
        <div class="col-lg-7 mb-3">
            <div class="dash-card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge-dot" style="background: var(--dash-info)"></span>
                        Composição financeira dos itens
                    </h3>
                    <span class="badge badge-light px-3 py-2">Garantia vs. faturamento</span>
                </div>
                <div class="card-body" style="height: 280px; position: relative;">
                    <canvas id="warrantyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-3">
            <div class="dash-card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge-dot" style="background: var(--dash-success)"></span>
                        Distribuição da composição
                    </h3>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    @php
                        $warranty = $warrantyStats['warranty'] ?? 0;
                        $paid = $warrantyStats['paid'] ?? 0;
                        $pending = $warrantyStats['pending_analysis'] ?? 0;
                        $total = max($warranty + $paid + $pending, 1);

                        $rows = [
                            ['label' => 'Garantia', 'value' => $warranty, 'color' => 'var(--dash-warning)'],
                            ['label' => 'Faturado (pago)', 'value' => $paid, 'color' => 'var(--dash-success)'],
                            ['label' => 'Pendente análise', 'value' => $pending, 'color' => 'var(--dash-info)'],
                        ];
                    @endphp

                    @foreach ($rows as $row)
                        @php $pct = round(($row['value'] / $total) * 100, 1); @endphp
                        <div class="warranty-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="warranty-label">{{ $row['label'] }}</span>
                                <span class="warranty-value" style="color: {{ $row['color'] }}">
                                    {{ $row['value'] }}
                                </span>
                            </div>
                            <div class="progress-warranty">
                                <div class="bar" style="width: {{ $pct }}%; background: {{ $row['color'] }}"></div>
                            </div>
                            <small class="warranty-pct">{{ $pct }}% do total</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const palette = {
            primary: '#4361ee', success: '#06d6a0', warning: '#ffd166',
            danger:  '#ef476f', info:    '#4cc9f0', purple:  '#7209b7',
            grid:    '#eef1f6'
        };

        Chart.defaults.font.family = "'Nunito','Segoe UI',system-ui,sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#8d99ae';

        /* ─── 1. Volumetria (linha com gradiente) ─── */
        const ctxMonth = document.getElementById('osByMonthChart');
        if (ctxMonth) {
            const g = ctxMonth.getContext('2d').createLinearGradient(0, 0, 0, 320);
            g.addColorStop(0, 'rgba(67,97,238,.25)');
            g.addColorStop(1, 'rgba(67,97,238,0)');

            new Chart(ctxMonth, {
                type: 'line',
                data: {
                    labels: {!! json_encode($osByMonth->pluck('month')) !!},
                    datasets: [{
                        label: 'OS',
                        data: {!! json_encode($osByMonth->pluck('count')) !!},
                        borderColor: palette.primary,
                        backgroundColor: g,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: palette.primary,
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            grid: { color: palette.grid, drawBorder: false },
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }

        /* ─── 2. Origem (donut) ─── */
        const ctxOrigin = document.getElementById('originChart');
        if (ctxOrigin) {
            new Chart(ctxOrigin, {
                type: 'doughnut',
                data: {
                    labels: ['Showroom SP', 'Showroom Limeira', 'Online', 'E-commerce', 'Eventos'],
                    datasets: [{
                        data: [
                            {{ $origins['showroom_sp'] ?? 0 }},
                            {{ $origins['limeira'] ?? 0 }},
                            {{ $origins['online'] ?? 0 }},
                            {{ $origins['ecommerce'] ?? 0 }},
                            {{ $origins['events'] ?? 0 }}
                        ],
                        backgroundColor: [
                            palette.primary, palette.info, palette.purple,
                            palette.success, palette.warning
                        ],
                        borderWidth: 3,
                        borderColor: '#fff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, padding: 12, usePointStyle: true }
                        }
                    }
                }
            });
        }

        /* ─── 3. Composição financeira (barras) ─── */
        const ctxWarranty = document.getElementById('warrantyChart');
        if (ctxWarranty) {
            new Chart(ctxWarranty, {
                type: 'bar',
                data: {
                    labels: ['Garantia', 'Faturado (pago)', 'Pendente análise'],
                    datasets: [{
                        data: [
                            {{ $warrantyStats['warranty'] ?? 0 }},
                            {{ $warrantyStats['paid'] ?? 0 }},
                            {{ $warrantyStats['pending_analysis'] ?? 0 }}
                        ],
                        backgroundColor: [palette.warning, palette.success, palette.info],
                        borderRadius: 8,
                        maxBarThickness: 55
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            grid: { color: palette.grid, drawBorder: false },
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }
    });
    </script>
@endpush