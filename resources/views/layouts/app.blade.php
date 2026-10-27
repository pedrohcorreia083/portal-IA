<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LBF Portal')</title>

    <!-- Google Fonts - Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet">

    <!-- CSS Global -->
    @vite(['resources/css/app.css'])

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">

    @stack('css')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- ===== NAVBAR ===== -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0">
            <!-- Left Navbar Links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
                </li>
            </ul>

            <!-- Right Navbar Links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications Dropdown Menu -->
                <!-- <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">15 Notificações</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 novas mensagens
                            <span class="float-right text-muted text-sm">3 mins</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-users mr-2"></i> 8 solicitações de amizade
                            <span class="float-right text-muted text-sm">12 horas</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-file mr-2"></i> 3 novos relatórios
                            <span class="float-right text-muted text-sm">2 dias</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">Ver todas notificações</a>
                    </div>
                </li> -->

                <!-- User Menu -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'Usuário' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="#" class="btn btn-default btn-flat">Perfil</a>
                            <a href="{{ route('logout') }}" class="btn btn-default btn-flat float-right"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Sair
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- ===== SIDEBAR ===== -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="brand-link text-center">
                <img src="{{ asset('images/logo/logo.png') }}" alt="LBF Portal"
                    class="brand-image img-circle elevation-3" style="opacity: .8; max-height: 33px;">
                <span class="brand-text font-weight-light"><b>LBF</b> PORTAL</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar User Panel (Optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" class="d-block">{{ Auth::user()->name ?? 'Usuário' }}</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Usuários -->
                        @if (auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}"
                                    class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Gestão de Usuários</p>
                                </a>
                            </li>
                        @endif

                        <!-- Ordens de Serviço -->
                        <li class="nav-item">
                            <a href="{{ route('service-orders.index') }}"
                                class="nav-link {{ request()->routeIs('service-orders.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Ordens de Serviço</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('technical-sheets.index') }}"
                                class="nav-link {{ request()->routeIs('technical-sheets.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>Ficha técnica</p>
                            </a>
                        </li>

                        <!-- Área Técnica -->
                        {{-- <li class="nav-item">
                            <a href="{{ route('service-orders.index') }}"
                                class="nav-link {{ request()->routeIs('service-orders.*') ? 'activee' : '' }}">
                                <i class="nav-icon fas fa-wrench"></i>
                                <p>Área Técnica</p>
                            </a>
                        </li> --}}

                        <!-- uploads de imagens -->
                        <li class="nav-item">
                            <a href="{{ route('uploads.index') }}"
                                class="nav-link {{ request()->routeIs('uploads.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file"></i>
                                <p>Arquivos</p>
                            </a>
                        </li>

                        <!-- Configurações -->
                        <!-- <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    Configurações
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Geral</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Segurança</p>
                                    </a>
                                </li>
                            </ul>
                        </li> -->

                        <!-- Relatórios -->
                        <!-- <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Relatórios</p>
                            </a>
                        </li> -->

                        <!-- Divisor -->
                        <!-- <li class="nav-header">ADMINISTRAÇÃO</li> -->

                        <!-- Sistema -->
                        <!-- <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-server"></i>
                                <p>Sistema</p>
                            </a>
                        </li> -->

                        <!-- Logs -->
                        <!-- <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Logs</p>
                            </a>
                        </li> -->
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- ===== CONTENT WRAPPER ===== -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('content-header', 'Dashboard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="content">
                <div class="container-fluid">
                    <!-- Alertas -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        <!-- ===== FOOTER ===== -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2026 <a href="{{ route('dashboard') }}">LBF Portal</a>.</strong>
            Todos os direitos reservados.
            <div class="float-right d-none d-sm-inline-block">
                <b>Versão</b> 3.0.0
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>

    <script>
        // Inicializar componentes do AdminLTE
        $(document).ready(function() {
            // Ativar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Ativar popovers
            $('[data-toggle="popover"]').popover();

            // Auto-dismiss alerts após 5 segundos
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>

    @stack('js')
</body>

</html>
