@extends('layouts.app')

@section('title', 'Gestão de Usuários')

@section('content-header')
    <div class="">
        <div>
            <h1 class="m-0">Gestão de Usuários</h1>
            <small class="text-muted">
                Gerencie os usuários do sistema
            </small>
        </div>

        <div>
            <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus mr-1"></i>
                Novo Usuário
            </a>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Usuários</li>
@endsection

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title m-0 font-weight-bold">
                    Lista de Usuários
                </h3>

                <span class="badge badge-secondary px-3 py-2">
                    {{ $users->total() }} usuários
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Empresa</th>
                            <th>Status</th>
                            <th class="text-center" width="170">
                                Ações
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong>
                                                {{ $user->full_name }}
                                            </strong>

                                            @if ($user->id === auth()->id())
                                                <div>
                                                    <small class="text-success">
                                                        Você
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $user->email }}
                                </td>

                                <td>
                                    @if ($user->isAdmin())
                                        <span class="badge badge-success px-3 py-2">
                                            <i class="fas fa-user-shield mr-1"></i>
                                            Administrador
                                        </span>
                                    @elseif ($user->type === 'technical')
                                        <span class="badge badge-warning px-3 py-2">
                                            <i class="fas fa-screwdriver-wrench mr-1"></i>
                                            Técnico
                                        </span>
                                    @else
                                        <span class="badge badge-info px-3 py-2">
                                            <i class="fas fa-user mr-1"></i>
                                            Usuário
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $user->company_name ?? 'Não informado' }}
                                </td>

                                <td>
                                    @if ($user->is_active)
                                        <span class="badge badge-success px-3 py-2">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Ativo
                                        </span>
                                    @else
                                        <span class="badge badge-danger px-3 py-2">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Inativo
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('users.show', $user) }}" class="icon-btn"
                                            title="Visualizar" aria-label="Visualizar {{ $user->name }}">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('users.edit', $user) }}" class="icon-btn"
                                            title="Editar" aria-label="Editar {{ $user->name }}">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                onsubmit="return confirm('Tem certeza que deseja deletar este usuário?')"
                                                class="d-inline">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="icon-btn is-danger" title="Excluir"
                                                        aria-label="Excluir {{ $user->name }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>

                                    <div class="text-muted">
                                        Nenhum usuário encontrado.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($users->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <small class="text-muted">
                        Mostrando
                        <strong>{{ $users->firstItem() }}</strong>
                        até
                        <strong>{{ $users->lastItem() }}</strong>
                        de
                        <strong>{{ $users->total() }}</strong>

                        {{ $users->total() > 1 ? 'usuários' : 'usuário' }}
                    </small>

                    <div>
                        {{ $users->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
