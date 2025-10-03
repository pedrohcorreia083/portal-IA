@extends('layouts.app')

@section('title', 'Perfil do Usuário')

@section('content-header')
Perfil do Usuário
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
<li class="breadcrumb-item active">Perfil</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle"
                        src="{{ asset('vendor/adminlte/dist/img/avatar.png') }}"
                        alt="User profile picture">
                </div>

                <h3 class="profile-username text-center">{{ $user->full_name }}</h3>

                <p class="text-muted text-center">
                    @if($user->isAdmin())
                    <span class="badge badge-success">Administrador</span>
                    @else
                    <span class="badge badge-info">Usuário</span>
                    @endif
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Email</b> <a class="float-right">{{ $user->email }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b>
                        <span class="float-right badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Cadastrado em</b>
                        <span class="float-right">{{ $user->created_at->format('d/m/Y') }}</span>
                    </li>
                </ul>

                @if(auth()->user()->isAdmin() || auth()->id() === $user->id)
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Editar Perfil
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações da Empresa</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <strong><i class="fas fa-building mr-1"></i> Nome da Empresa</strong>
                        <p class="text-muted">
                            {{ $user->company_name ?? 'Não informado' }}
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <strong><i class="fas fa-id-card mr-1"></i> CNPJ</strong>
                        <p class="text-muted">
                            {{ $user->cnpj ?? 'Não informado' }}
                        </p>
                    </div>
                </div>

                <hr>

                <strong><i class="fas fa-info-circle mr-1"></i> Informações Adicionais</strong>
                <p class="text-muted">
                    @if($user->isAdmin())
                    Este usuário possui acesso total ao sistema.
                    @else
                    Este usuário possui permissões limitadas de acordo com seu perfil.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection