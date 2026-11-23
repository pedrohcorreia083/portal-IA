@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content-header')
Editar Usuário: {{ $user->full_name }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações Básicas</h3>
            </div>
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nome *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Sobrenome *</label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                    id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="type">Tipo de Usuário *</label>
                        <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="user" {{ old('type', $user->type) == 'user' ? 'selected' : '' }}>Usuário</option>
                            <option value="technical" {{ old('type', $user->type) == 'technical' ? 'selected' : '' }}>Técnico</option>
                            <option value="admin" {{ old('type', $user->type) == 'admin' ? 'selected' : '' }}>Administrador</option>
                        </select>
                        @error('type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_name">Nome da Empresa</label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                            id="company_name" name="company_name" value="{{ old('company_name', $user->company_name) }}">
                        @error('company_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" class="form-control @error('cnpj') is-invalid @enderror"
                            id="cnpj" name="cnpj" value="{{ old('cnpj', $user->cnpj) }}"
                            placeholder="00.000.000/0000-00">
                        @error('cnpj')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Nova Senha (deixe em branco para manter a atual)</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password">
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirmar Nova Senha</label>
                                <input type="password" class="form-control"
                                    id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Usuário Ativo</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Usuário
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-default">Cancelar</a>

                    @if($user->id !== auth()->id())
                    @if($user->is_active)
                    <a href="{{ route('users.toggle-status', $user) }}"
                        class="btn btn-warning float-right"
                        onclick="return confirm('Tem certeza que deseja desativar este usuário?')">
                        <i class="fas fa-ban"></i> Desativar
                    </a>
                    @else
                    <a href="{{ route('users.toggle-status', $user) }}"
                        class="btn btn-success float-right"
                        onclick="return confirm('Tem certeza que deseja ativar este usuário?')">
                        <i class="fas fa-check"></i> Ativar
                    </a>
                    @endif
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações do Sistema</h3>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> {{ $user->id }}</p>
                <p><strong>Cadastrado em:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Última atualização:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>

                @if($user->email_verified_at)
                <p><strong>Email verificado em:</strong> {{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                @else
                <p><strong>Email:</strong> <span class="badge badge-warning">Não verificado</span></p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Máscara para CNPJ
    document.getElementById('cnpj').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/(\d{2})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1/$2')
                .replace(/(\d{4})(\d{1,2})/, '$1-$2')
                .replace(/(-\d{2})\d+?$/, '$1');
            e.target.value = value;
        }
    });
</script>
@endpush