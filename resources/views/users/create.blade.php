@extends('layouts.app')

@section('title', 'Novo Usuário')

@section('content-header')
Novo Usuário
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
<li class="breadcrumb-item active">Novo</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Cadastrar Novo Usuário</h3>
    </div>
    <form action="{{ route('users.store') }}" method="POST">
        @csrf <!-- ✅ VERIFIQUE SE ESTÁ PRESENTE -->

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nome *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="name" name="name" value="{{ old('name') }}" required>
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
                            id="last_name" name="last_name" value="{{ old('last_name') }}" required>
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
                    id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Senha *</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" required>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Senha *</label>
                        <input type="password" class="form-control"
                            id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="type">Tipo de Usuário *</label>
                <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                    <option value="">Selecione...</option>
                    <option value="user" {{ old('type') == 'user' ? 'selected' : '' }}>Usuário</option>
                    <option value="admin" {{ old('type') == 'admin' ? 'selected' : '' }}>Administrador</option>
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
                    id="company_name" name="company_name" value="{{ old('company_name') }}">
                @error('company_name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cnpj">CNPJ</label>
                <input type="text" class="form-control @error('cnpj') is-invalid @enderror"
                    id="cnpj" name="cnpj" value="{{ old('cnpj') }}"
                    placeholder="00.000.000/0000-00">
                @error('cnpj')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Cadastrar Usuário</button>
            <a href="{{ route('users.index') }}" class="btn btn-default">Cancelar</a>
        </div>
    </form>
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