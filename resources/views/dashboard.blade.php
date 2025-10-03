@extends('layouts.app')

@section('title', 'Dashboard')

@section('content-header')
Dashboard
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <!-- Cards de Resumo -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_os'] }}</h3>
                <p>Total de OS</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <a href="{{ route('service-orders.index') }}" class="small-box-footer">
                Mais info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['os_pending'] }}</h3>
                <p>OS Pendentes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('service-orders.index', ['status' => 'pending']) }}" class="small-box-footer">
                Mais info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['os_in_progress'] }}</h3>
                <p>OS em Andamento</p>
            </div>
            <div class="icon">
                <i class="fas fa-tools"></i>
            </div>
            <a href="{{ route('service-orders.index', ['status' => 'in_progress']) }}" class="small-box-footer">
                Mais info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['total_users'] }}</h3>
                <p>Usuários Cadastrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('users.index') }}" class="small-box-footer">
                Mais info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">

    <!-- OS Recentes -->
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">OS Recentes</h3>
                <div class="card-tools">
                    <span class="badge badge-primary">{{ $recentOs->count() }} novas</span>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    @forelse($recentOs as $os)
                    <li class="item">
                        <div class="product-info">
                            <a href="{{ route('service-orders.show', $os) }}" class="product-title">
                                {{ $os->order_number }}
                                <span class="badge badge-{{ $os->status === 'completed' ? 'success' : ($os->status === 'cancelled' ? 'danger' : 'warning') }} float-right">
                                    {{ ucfirst($os->status) }}
                                </span>
                            </a>
                            <span class="product-description">
                                {{ $os->client_name }} - {{ $os->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="item">
                        <div class="product-info">
                            <span class="product-description text-muted">
                                Nenhuma OS recente
                            </span>
                        </div>
                    </li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('service-orders.index') }}" class="uppercase">Ver Todas as OS</a>
            </div>
        </div>
    </div>
</div>

<!-- Ações Rápidas -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ações Rápidas</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('service-orders.create') }}" class="btn btn-app bg-success">
                            <i class="fas fa-plus"></i> Nova OS
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('service-orders.index') }}" class="btn btn-app bg-info">
                            <i class="fas fa-list"></i> Listar OS
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('users.index') }}" class="btn btn-app bg-warning">
                            <i class="fas fa-users"></i> Usuários
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="#" class="btn btn-app bg-purple">
                            <i class="fas fa-chart-bar"></i> Relatórios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection