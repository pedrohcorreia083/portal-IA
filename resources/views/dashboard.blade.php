@extends('layouts.app')

@section('title', 'Dashboard')

@section('content-header')
    <span class="text-dark font-weight-bold">Dashboard de Operações</span>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Linha 1 — KPIs (cards) -->
    <div class="row">
        <div class="col-lg-3 col-6 mb-3">
            <div class="small-box shadow-sm border-0 bg-white h-100 position-relative"
                style="border-radius: 12px; overflow: hidden; transition: transform .2s;">
                <div class="inner p-3">
                    <h3 class="text-info font-weight-bold mb-1">{{ $stats['total_os'] }}</h3>
                    <p class="text-muted text-sm font-weight-medium mb-0">Total de OS</p>
                </div>
                <div class="icon position-absolute" style="right: 15px; top: 15px; opacity: 0.15; font-size: 2.5rem;">
                    <i class="fas fa-file-alt text-info"></i>
                </div>
                <a href="{{ route('service-orders.index') }}"
                    class="small-box-footer bg-light text-info text-xs py-2 border-top">
                    Mais info <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-3">
            <div class="small-box shadow-sm border-0 bg-white h-100 position-relative"
                style="border-radius: 12px; overflow: hidden; transition: transform .2s;">
                <div class="inner p-3">
                    <h3 class="text-warning font-weight-bold mb-1">{{ $stats['os_pending'] }}</h3>
                    <p class="text-muted text-sm font-weight-medium mb-0">Pendentes</p>
                </div>
                <div class="icon position-absolute" style="right: 15px; top: 15px; opacity: 0.15; font-size: 2.5rem;">
                    <i class="fas fa-clock text-warning"></i>
                </div>
                <a href="{{ route('service-orders.index', ['status' => 'created']) }}"
                    class="small-box-footer bg-light text-warning text-xs py-2 border-top">
                    Mais info <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-3">
            <div class="small-box shadow-sm border-0 bg-white h-100 position-relative"
                style="border-radius: 12px; overflow: hidden; transition: transform .2s;">
                <div class="inner p-3">
                    <h3 class="text-success font-weight-bold mb-1">{{ $stats['os_completed_month'] }}</h3>
                    <p class="text-muted text-sm font-weight-medium mb-0">No Mês</p>
                </div>
                <div class="icon position-absolute" style="right: 15px; top: 15px; opacity: 0.15; font-size: 2.5rem;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <a href="{{ route('service-orders.index', ['status' => 'completed']) }}"
                    class="small-box-footer bg-light text-success text-xs py-2 border-top">
                    Mais info <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-3">
            <div class="small-box shadow-sm border-0 bg-white h-100 position-relative"
                style="border-radius: 12px; overflow: hidden; transition: transform .2s;">
                <div class="inner p-3">
                    <h3 class="text-purple font-weight-bold mb-1" style="color: #6f42c1;">{{ $stats['warranty_rate'] }}%
                    </h3>
                    <p class="text-muted text-sm font-weight-medium mb-0">Taxa de Garantia</p>
                </div>
                <div class="icon position-absolute" style="right: 15px; top: 15px; opacity: 0.15; font-size: 2.5rem;">
                    <i class="fas fa-shield-alt" style="color: #6f42c1;"></i>
                </div>
                <a href="#" class="small-box-footer bg-light text-xs py-2 border-top" style="color: #6f42c1;">
                    Detalhes <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Linha 2 — Gráficos Principais -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card card-custom border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h3 class="card-title text-dark font-weight-bold text-md">
                        <i class="fas fa-chart-line text-primary mr-2"></i> Volumetria de OS (Últimos 12 Meses)
                    </h3>
                </div>
                <div class="card-body">
                    <div style="height: 320px; position: relative;">
                        <canvas id="osByMonthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Linha 3 — Operacional & Ranking -->
        <div class="col-md-6 mb-4">
            <div class="card card-custom border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h3 class="card-title text-dark font-weight-bold text-md">
                        <i class="fas fa-map-marker-alt text-danger mr-2"></i> Canais de Origem das OS
                    </h3>
                </div>
                <div class="card-body">
                    <div style="height: 280px; position: relative;">
                        <canvas id="originChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Linha 4 — Financeiro/Técnico -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card card-custom border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h3 class="card-title text-dark font-weight-bold text-md">
                        <i class="fas fa-balance-scale text-success mr-2"></i> Composição Financeira: Garantia vs
                        Faturamento (Itens)
                    </h3>
                </div>
                <div class="card-body">
                    <div style="height: 250px; position: relative;">
                        <canvas id="warrantyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estilos Inline complementares para efeito hover nos cards -->
    <style>
        .small-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }
    </style>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Paleta de Cores Premium Modernas
            const palette = {
                blue: '#4e73df',
                blueLight: '#eaecf4',
                green: '#1cc88a',
                teal: '#36b9cc',
                yellow: '#f6c23e',
                red: '#e74a3b',
                purple: '#6f42c1',
                orange: '#fd7e14',
                grayLight: '#f8f9fc',
                gridColor: '#f1f1f1'
            };

            // Configurações Globais de Fonte do Chart.js
            Chart.defaults.font.family = "'Nunito', 'Segoe UI', system-ui, -apple-system, sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#858796';

            // Gráfico 1: OS por Mês (Linha Suave com Área preenchida)
            const ctxOsByMonth = document.getElementById('osByMonthChart');
            if (ctxOsByMonth) {
                new Chart(ctxOsByMonth, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($osByMonth->pluck('month')) !!},
                        datasets: [{
                            label: 'Ordens de Serviço',
                            data: {!! json_encode($osByMonth->pluck('count')) !!},
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            borderColor: palette.blue,
                            pointBackgroundColor: palette.blue,
                            pointBorderColor: '#fff',
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: palette.blue,
                            tension: 0.35,
                            fill: true,
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                grid: {
                                    color: palette.gridColor
                                },
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico 2: Distribuição por Status (Donut com espaçamento interno)
            const ctxStatus = document.getElementById('statusDistributionChart');
            if (ctxStatus) {
                new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendente', 'Em Análise', 'Aprovada', 'Concluída', 'Cancelada'],
                        datasets: [{
                            data: [
                                {{ $stats['os_pending'] ?? 0 }},
                                {{ $stats['os_analysis'] ?? 0 }},
                                {{ $stats['os_repair'] ?? 0 }},
                                {{ $stats['os_completed'] ?? 0 }},
                                {{ $stats['os_cancelled'] ?? 0 }}
                            ],
                            backgroundColor: [palette.yellow, palette.teal, palette.blue, palette
                                .green, palette.red
                            ],
                            borderWidth: 4,
                            borderColor: '#fff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15,
                                    usePointStyle: true
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // Gráfico 3: Origem das OS (Barras Horizontais Elegantes)
            const ctxOrigin = document.getElementById('originChart');
            if (ctxOrigin) {
                new Chart(ctxOrigin, {
                    type: 'bar',
                    data: {
                        labels: ['Showroom SP', 'Showroom Limeira', 'Online', 'Ecommerce', 'Eventos'],
                        datasets: [{
                            data: [
                                {{ $origins['showroom_sp'] ?? 0 }},
                                {{ $origins['limeira'] ?? 0 }},
                                {{ $origins['online'] ?? 0 }},
                                {{ $origins['ecommerce'] ?? 0 }},
                                {{ $origins['events'] ?? 0 }}
                            ],
                            backgroundColor: palette.purple,
                            borderRadius: 6,
                            maxBarThickness: 25
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: palette.gridColor
                                },
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico 4: Garantia vs Pago
            const ctxWarranty = document.getElementById('warrantyChart');
            if (ctxWarranty) {
                new Chart(ctxWarranty, {
                    type: 'bar',
                    data: {
                        labels: ['Garantia', 'Faturado (Pago)', 'Pendente Análise'],
                        datasets: [{
                            data: [
                                {{ $warrantyStats['warranty'] ?? 0 }},
                                {{ $warrantyStats['paid'] ?? 0 }},
                                {{ $warrantyStats['pending_analysis'] ?? 0 }}
                            ],
                            backgroundColor: [palette.orange, palette.green, palette.yellow],
                            borderRadius: 6,
                            maxBarThickness: 50
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                grid: {
                                    color: palette.gridColor
                                },
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
