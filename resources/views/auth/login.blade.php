<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - LBF Portal</title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        .login-page {
            background: #857874;
            background: linear-gradient(90deg, rgba(133, 120, 116, 1) 0%, rgba(194, 180, 167, 1) 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-box {
            width: 360px;
        }

        .login-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: none;
        }

        .btn-primary {
            background: #857874;
            border: none;
        }

        .btn-primary:hover {
            background: #C2B4A7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="login-page">
    <div class="login-box">
        <!-- Logo -->
        <div class="login-logo text-center mb-4">
            LBF Portal
        </div>

        <!-- Card de Login -->
        <div class="card login-card">
            <div class="card-body login-card-body">
                <p class="login-box-msg text-dark">Faça login para acessar o sistema</p>

                <form action="{{ route('login') }}" method="post">
                    @csrf

                    <!-- Email -->
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            placeholder="E-mail" value="{{ old('email') }}" required autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <!-- Senha -->
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                            placeholder="Senha" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember" class="text-dark">
                                    Lembrar-me
                                </label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                Entrar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Links -->
                <div class="social-auth-links text-center mb-3 mt-3">
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-dark">
                        <i class="fas fa-key mr-2"></i>Esqueci minha senha
                    </a>
                    <br>
                    @endif

                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="text-dark">
                        <i class="fas fa-user-plus mr-2"></i>Criar nova conta
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>

</body>

</html>