<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#E7E3DF">
    <title>Bem-vindo | LBF Portal</title>

    <!-- Fonte de corpo definida no manual da marca -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --dark: #3D3935;
            --taupe: #857874;
            --sand: #C1B2A2;
            --cream: #E7E3DF;
            --white: #FFFFFF;
            --danger: #B45353;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            color: var(--dark);
            background: var(--cream);
            -webkit-font-smoothing: antialiased;
        }

        button,
        input {
            font: inherit;
        }

        button,
        a,
        input {
            -webkit-tap-highlight-color: transparent;
        }

        button:focus-visible,
        a:focus-visible,
        input:focus-visible {
            outline: 2px solid var(--taupe);
            outline-offset: 3px;
        }

        .login-layout {
            display: grid;
            grid-template-columns: 1.08fr 0.92fr;
            min-height: 100vh;
            min-height: 100dvh;
        }

        /* PAINEL INSTITUCIONAL */

        .brand-panel {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 58px clamp(35px, 6vw, 105px);
            background: linear-gradient(145deg,
                    #E7E3DF 0%,
                    #DCD3CB 52%,
                    #C1B2A2 100%);
            isolation: isolate;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            opacity: .38;
            background: radial-gradient(ellipse at 25% 35%,
                    rgba(255, 255, 255, .8),
                    transparent 65%);
        }

        .brand-top,
        .brand-content,
        .brand-footer {
            position: relative;
            z-index: 2;
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 14px;
            color: var(--taupe);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .brand-top::before {
            content: "";
            width: 28px;
            height: 1px;
            background: var(--taupe);
        }

        .brand-content {
            max-width: 640px;
            animation: fadeUp 1s ease both;
        }

        .eyebrow {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            color: var(--taupe);
            text-transform: uppercase;
        }

        .eyebrow::before {
            content: "";
            width: 30px;
            height: 1px;
            background: var(--taupe);
        }

        .brand-title {
            margin: 0;
            font-family: 'Alta', Georgia, 'Times New Roman', serif;
            font-size: clamp(54px, 6.3vw, 104px);
            font-weight: 400;
            line-height: 1.06;
            letter-spacing: -2px;
        }

        .brand-title em {
            font-style: normal;
            color: var(--taupe);
        }

        .brand-description {
            margin-top: 32px;
            max-width: 390px;
            font-size: 13px;
            line-height: 2;
            color: #655C57;
        }

        .brand-divider {
            width: 62px;
            height: 1px;
            margin-top: 36px;
            background: var(--taupe);
        }

        .brand-footer {
            font-size: 10px;
            color: var(--taupe);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* FORMAS DECORATIVAS ANIMADAS */

        .orb {
            position: absolute;
            z-index: 0;
            width: 410px;
            height: 410px;
            border: 1px solid rgba(133, 120, 116, .22);
            border-radius: 50%;
            pointer-events: none;
        }

        .orb-one {
            right: -180px;
            top: 10%;
            animation: floatOne 14s ease-in-out infinite;
        }

        .orb-two {
            right: -120px;
            top: 17%;
            width: 315px;
            height: 315px;
            animation: floatTwo 18s ease-in-out infinite;
        }

        .orb-three {
            right: -65px;
            top: 23%;
            width: 210px;
            height: 210px;
            background: rgba(255, 255, 255, .09);
            animation: floatOne 12s ease-in-out infinite reverse;
        }

        /* ÁREA DO FORMULÁRIO */

        .form-panel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            padding: 55px clamp(28px, 5vw, 90px);
        }

        .login-container {
            width: 100%;
            max-width: 410px;
            animation: fadeUp .85s .12s both;
        }

        .logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 57px;
        }

        .logo-wrap img {
            display: block;
            max-width: 200px;
            max-height: 105px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .welcome-label {
            margin: 0 0 15px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--taupe);
        }

        .welcome-title {
            margin: 0 0 12px;
            font-family: 'Alta', Georgia, 'Times New Roman', serif;
            font-size: clamp(32px, 3vw, 43px);
            font-weight: 400;
            letter-spacing: -.5px;
            line-height: 1.2;
        }

        .welcome-description {
            margin: 0 0 43px;
            color: var(--taupe);
            font-size: 12px;
            line-height: 1.8;
        }

        .field {
            margin-bottom: 24px;
        }

        .field label {
            display: block;
            margin-bottom: 11px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .3px;
            color: var(--dark);
        }

        .input-wrap {
            display: flex;
            align-items: center;
            position: relative;
            min-height: 55px;
            border: 1px solid #E1DCD7;
            border-radius: 5px;
            background: var(--white);
            transition:
                border-color .25s ease,
                box-shadow .25s ease,
                transform .25s ease;
        }

        .input-wrap:focus-within {
            border-color: var(--taupe);
            box-shadow: 0 0 0 4px rgba(133, 120, 116, .09);
        }

        .input-wrap svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            color: #A49A94;
        }

        .field-icon {
            margin-left: 17px;
        }

        .input-wrap input {
            width: 100%;
            min-width: 0;
            height: 53px;
            padding: 0 14px;
            background: transparent;
            border: 0;
            outline: none;
            font-size: 12px;
            color: var(--dark);
        }

        .input-wrap input::placeholder {
            color: #A9A09B;
        }

        .input-wrap input:focus {
            outline: none;
        }

        .input-wrap.invalid {
            border-color: var(--danger);
        }

        .error-message {
            margin-top: 8px;
            font-size: 11px;
            color: var(--danger);
            line-height: 1.6;
        }

        .toggle-password {
            display: grid;
            place-items: center;
            width: 44px;
            height: 48px;
            flex-shrink: 0;
            margin-right: 5px;
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--taupe);
            cursor: pointer;
        }

        .toggle-password:hover {
            color: var(--dark);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin: 8px 0 35px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 11px;
            color: var(--taupe);
            cursor: pointer;
        }

        .remember input {
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: var(--taupe);
            cursor: pointer;
        }

        .forgot-link {
            font-size: 11px;
            font-weight: 600;
            color: var(--taupe);
            text-decoration: none;
            transition: color .2s ease;
        }

        .forgot-link:hover {
            color: var(--dark);
        }

        .submit-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            width: 100%;
            min-height: 55px;
            padding: 14px 22px;
            border: 1px solid var(--taupe);
            border-radius: 5px;
            background: var(--taupe);
            color: var(--white);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition:
                background .3s ease,
                transform .3s ease,
                box-shadow .3s ease;
        }

        .submit-button:hover {
            background: var(--dark);
            border-color: var(--dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(61, 57, 53, .15);
        }

        .submit-button svg {
            width: 17px;
            height: 17px;
            transition: transform .3s ease;
        }

        .submit-button:hover svg {
            transform: translateX(5px);
        }

        .submit-button:disabled {
            opacity: .7;
            cursor: wait;
            transform: none;
        }

        .form-footer {
            margin-top: 58px;
            text-align: center;
            color: #AAA19B;
            font-size: 10px;
            line-height: 1.8;
        }

        /* ANIMAÇÕES */

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatOne {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(-22px, 24px);
            }
        }

        @keyframes floatTwo {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(22px, -30px);
            }
        }

        /* RESPONSIVIDADE */

        @media (max-width: 900px) {
            .login-layout {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                min-height: 245px;
                padding: 35px 32px;
            }

            .brand-content {
                margin-top: 30px;
            }

            .brand-title {
                font-size: 42px;
                letter-spacing: -1px;
            }

            .brand-description,
            .brand-divider,
            .brand-footer,
            .eyebrow {
                display: none;
            }

            .orb-one {
                right: -160px;
                top: -100px;
            }

            .orb-two {
                right: -110px;
                top: -50px;
            }

            .orb-three {
                right: -40px;
                top: 0;
            }

            .form-panel {
                padding: 55px 25px 40px;
            }

            .login-container {
                max-width: 450px;
            }

            .logo-wrap {
                margin-bottom: 40px;
            }

            .form-footer {
                margin-top: 45px;
            }
        }

        @media (max-width: 480px) {
            .brand-panel {
                min-height: 205px;
                padding: 28px 24px;
            }

            .brand-title {
                font-size: 35px;
            }

            .brand-top {
                font-size: 9px;
            }

            .form-panel {
                padding: 42px 24px;
                align-items: flex-start;
            }

            .logo-wrap img {
                max-width: 160px;
                max-height: 85px;
            }

            .welcome-description {
                margin-bottom: 32px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>
    <main class="login-layout">

        <!-- PAINEL DA MARCA -->
        <section class="brand-panel" aria-label="Apresentação LBF">

            <div class="orb orb-one" aria-hidden="true"></div>
            <div class="orb orb-two" aria-hidden="true"></div>
            <div class="orb orb-three" aria-hidden="true"></div>

            <div class="brand-top">
                LBF Portal
            </div>

            <div class="brand-content">
                <div class="eyebrow">
                    Bem-vindo à LBF
                </div>

                <h1 class="brand-title">
                    Elegância<br>
                    em cada<br>
                    <em>detalhe.</em>
                </h1>

                <p class="brand-description">
                    Um espaço criado para transformar ideias,
                    conectar processos e acompanhar cada
                    etapa com excelência.
                </p>

                <div class="brand-divider"></div>
            </div>

            <div class="brand-footer">
                Desde 2010 &nbsp;•&nbsp; LBF Semijoias
            </div>

        </section>

        <!-- PAINEL DE LOGIN -->
        <section class="form-panel">
            <div class="login-container">

                <div class="logo-wrap">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="LBF">
                </div>

                <p class="welcome-label">
                    Acesso ao sistema
                </p>

                <h2 class="welcome-title">
                    Bem-vindo de volta.
                </h2>

                <p class="welcome-description">
                    Insira suas credenciais para continuar.
                </p>

                <form id="loginForm" action="{{ route('login') }}" method="POST">
                    @csrf

                    <!-- E-MAIL -->
                    <div class="field">
                        <label for="email">E-mail</label>

                        <div class="input-wrap @error('email') invalid @enderror">
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="m3 7 9 6 9-6" />
                            </svg>

                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                placeholder="seu@email.com" autocomplete="username" required autofocus
                                @error('email') aria-invalid="true" @enderror>
                        </div>

                        @error('email')
                            <div class="error-message" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- SENHA -->
                    <div class="field">
                        <label for="password">Senha</label>

                        <div class="input-wrap @error('password') invalid @enderror">
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="5" y="10" width="14" height="11" rx="2" />
                                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>

                            <input id="password" type="password" name="password" placeholder="Digite sua senha"
                                autocomplete="current-password" required
                                @error('password') aria-invalid="true" @enderror>

                            <button class="toggle-password" type="button" id="togglePassword"
                                aria-label="Mostrar senha" aria-pressed="false">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <div class="error-message" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- OPÇÕES -->
                    <div class="form-options">
                        <label class="remember" for="remember">
                            <input id="remember" type="checkbox" name="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            Manter conectado
                        </label>

                        @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">
                                Esqueceu sua senha?
                            </a>
                        @endif
                    </div>

                    <!-- BOTÃO -->
                    <button type="submit" class="submit-button" id="submitButton">
                        <span id="buttonText">ACESSAR PORTAL</span>

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>
                    </button>

                </form>

                <div class="form-footer">
                    © {{ date('Y') }} LBF Semijoias<br>
                    Ambiente de acesso restrito.
                </div>

            </div>
        </section>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const password = document.getElementById('password');
            const toggle = document.getElementById('togglePassword');
            const form = document.getElementById('loginForm');
            const submitButton = document.getElementById('submitButton');
            const buttonText = document.getElementById('buttonText');

            toggle.addEventListener('click', () => {
                const show = password.type === 'password';

                password.type = show ? 'text' : 'password';

                toggle.setAttribute(
                    'aria-label',
                    show ? 'Ocultar senha' : 'Mostrar senha'
                );

                toggle.setAttribute('aria-pressed', String(show));
            });

            form.addEventListener('submit', (event) => {
                if (!form.checkValidity()) {
                    return;
                }

                submitButton.disabled = true;
                buttonText.textContent = 'ACESSANDO...';
            });
        });
    </script>
</body>

</html>
