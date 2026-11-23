@php
    $brand = config('brand');
    $brandName = $brand['name'] ?? 'Portal';
    $brandProduct = $brand['product'] ?? 'Portal';
    $brandLegal = $brand['legal_name'] ?? $brandName;
    $brandLogo = $brand['logo'] ?? null;
    $brandColors = $brand['colors'] ?? [];
    $user = auth()->user();
    $initials = collect(explode(' ', trim(($user->name ?? '') . ' ' . ($user->last_name ?? ''))))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $brandColors['cream'] ?? '#E7E3DF' }}">
    <title>@yield('title', 'Painel') · {{ $brandName }} {{ $brandProduct }}</title>

    {{-- Tipografia da marca --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    {{-- Base: AdminLTE/Bootstrap carregado ANTES do design system --}}
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">

    {{-- Design system da marca (sobrescreve a base) --}}
    @vite(['resources/css/app.css'])

    {{-- Cores vindas de config/brand.php --}}
    <style>
        :root {
            --brand-dark: {{ $brandColors['dark'] ?? '#3D3935' }};
            --brand-taupe: {{ $brandColors['taupe'] ?? '#857874' }};
            --brand-sand: {{ $brandColors['sand'] ?? '#C1B2A2' }};
            --brand-cream: {{ $brandColors['cream'] ?? '#E7E3DF' }};
            --brand-white: {{ $brandColors['white'] ?? '#FFFFFF' }};
            --brand-danger: {{ $brandColors['danger'] ?? '#B45353' }};
            --brand-success: {{ $brandColors['success'] ?? '#4F7A5B' }};
            --brand-warning: {{ $brandColors['warning'] ?? '#B8894A' }};
            --brand-info: {{ $brandColors['info'] ?? '#6B7C8C' }};
        }
    </style>

    @stack('css')
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        {{-- ============ SIDEBAR ============ --}}
        <aside class="main-sidebar">
            <div class="sidebar">
                <a href="{{ route('dashboard') }}" class="brand-link">
                    <span class="brand-mark">
                        @if ($brandLogo && file_exists(public_path($brandLogo)))
                            <img src="{{ asset($brandLogo) }}" alt="{{ $brandName }}">
                        @else
                            <span>{{ mb_substr($brandName, 0, 1) }}</span>
                        @endif
                    </span>
                    <span class="brand-copy">
                        <strong>{{ $brandName }}</strong>
                        <small>{{ $brandProduct }}</small>
                    </span>
                </a>

                @if ($user)
                    <div class="user-panel">
                        <span class="avatar">{{ $initials ?: '?' }}</span>
                        <span class="info">
                            <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
                            <small>{{ $user->isAdmin() ? 'Administrador' : ($user->type === 'technical' ? 'Técnico' : 'Usuário') }}</small>
                        </span>
                    </div>
                @endif

                <nav class="nav-sidebar" aria-label="Menu principal">
                    <ul class="nav nav-pills nav-sidebar flex-column" role="menu">

                        <li class="nav-header">Operação</li>

                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-th-large"></i>
                                <p>Visão geral</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('service-orders.index') }}"
                               class="nav-link {{ request()->routeIs('service-orders.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-invoice"></i>
                                <p>Ordens de serviço</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('technical-sheets.index') }}"
                               class="nav-link {{ request()->routeIs('technical-sheets.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clipboard-check"></i>
                                <p>Fichas técnicas</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('uploads.index') }}"
                               class="nav-link {{ request()->routeIs('uploads.*') ? 'active' : '' }}">
                                <i class="nav-icon far fa-images"></i>
                                <p>Biblioteca de imagens</p>
                            </a>
                        </li>

                        @if ($user && $user->isAdmin())
                            <li class="nav-header">Administração</li>

                            <li class="nav-item">
                                <a href="{{ route('users.index') }}"
                                   class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Usuários e acessos</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        {{-- ============ TOPBAR ============ --}}
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" id="sidebarToggle" role="button" aria-label="Alternar menu">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-inline-block">
                    <span class="topbar-title">{{ $brandName }} {{ $brandProduct }}</span>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link p-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="user-chip">
                            <span class="avatar">{{ $initials ?: '?' }}</span>
                            <span class="d-none d-sm-inline">{{ $user->name ?? 'Usuário' }}</span>
                            <i class="fas fa-angle-down" style="font-size:9px;opacity:.5"></i>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <span class="dropdown-header">{{ $user->email ?? '' }}</span>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('users.show', $user) }}" class="dropdown-item">
                            <i class="far fa-user"></i> Meu perfil
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Encerrar sessão
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        {{-- ============ CONTEÚDO ============ --}}
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    @hasSection('breadcrumb')
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Início</a></li>
                            @yield('breadcrumb')
                        </ol>
                    @endif

                    <div class="page-head">
                        <div>
                            @yield('content-header', 'Painel')
                        </div>

                        @hasSection('page-actions')
                            <div class="page-actions">
                                @yield('page-actions')
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <main class="content">
                <div class="container-fluid">
                    @include('partials.flash')

                    @yield('content')
                </div>
            </main>
        </div>

        {{-- ============ RODAPÉ ============ --}}
        <footer class="main-footer">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>
                    &copy; {{ date('Y') }} {{ $brandLegal }} · Ambiente de acesso restrito
                </span>
                <span class="text-muted">
                    {{ $brandName }} {{ $brandProduct }}
                </span>
            </div>
        </footer>
    </div>

    {{-- Scripts base (AdminLTE depende de jQuery) --}}
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @vite(['resources/js/app.js'])

    <script>
        // Alternância da sidebar (desktop recolhe, mobile abre com sobreposição).
        (function () {
            const toggle = document.getElementById('sidebarToggle');

            toggle?.addEventListener('click', function (event) {
                event.preventDefault();

                if (window.innerWidth < 992) {
                    document.body.classList.toggle('sidebar-open');
                } else {
                    document.body.classList.toggle('sidebar-collapse');
                }
            });

            // Fecha a gaveta ao clicar fora dela (mobile).
            document.addEventListener('click', function (event) {
                if (window.innerWidth >= 992) return;
                if (!document.body.classList.contains('sidebar-open')) return;

                const insideSidebar = event.target.closest('.main-sidebar');
                const isToggle = event.target.closest('#sidebarToggle');

                if (!insideSidebar && !isToggle) {
                    document.body.classList.remove('sidebar-open');
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 992) {
                    document.body.classList.remove('sidebar-open');
                }
            });
        })();

        // Tooltips/popovers e fechamento automático dos alertas.
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();

            setTimeout(function () {
                $('.alert-auto-dismiss').fadeOut(300);
            }, 6000);
        });
    </script>

    @stack('js')
</body>

</html>
