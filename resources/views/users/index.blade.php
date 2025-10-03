@extends('layouts.app')

@section('title', 'Gestão de Usuários')

@section('content-header')
Gestão de Usuários
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Usuários</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Usuários</h3>
        <div class="card-tools">
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Novo Usuário
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th>Empresa</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge badge-{{ $user->isAdmin() ? 'success' : 'info' }}">
                            {{ $user->isAdmin() ? 'Administrador' : 'Usuário' }}
                        </span>
                    </td>
                    <td>{{ $user->company_name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('users.show', $user) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Tem certeza que deseja deletar este usuário?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhum usuário encontrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $users->links() }}
    </div>
</div>
@endsection