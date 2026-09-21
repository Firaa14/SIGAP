<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SIGAP</title>

    <script>
        (function () {
            const savedTheme = localStorage.getItem('sigap-theme');
            document.documentElement.dataset.theme = savedTheme === 'dark' ? 'dark' : 'light';
        })();
    </script>

    <style>
        :root {
            --login-surface: rgba(255, 255, 255, 0.93);
            --login-heading: #1e293b;
            --login-text: #334155;
            --login-muted: #64748b;
            --login-input: rgba(248, 250, 252, 0.95);
            --login-border: #cbd5e1;
        }

        [data-theme="dark"] {
            --login-surface: rgba(14, 29, 56, 0.96);
            --login-heading: #ffffff;
            --login-text: #dbe7f5;
            --login-muted: #a8b9d1;
            --login-input: rgba(25, 48, 82, 0.96);
            --login-border: rgba(255, 255, 255, 0.18);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                linear-gradient(rgba(0, 0, 0, 0.68),
                    rgba(0, 0, 0, 0.68)),
                url('/images/alt-PLTA.PNG');

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;

            position: relative;
        }

        .login-wrapper {
            width: 900px;
            min-height: 540px;

            background: transparent;

            border-radius: 18px;
            overflow: hidden;

            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.45);

            display: flex;

            position: relative;
            z-index: 2;
        }

        .theme-toggle {
            position: fixed;
            top: 22px;
            right: 22px;
            z-index: 5;
            width: 38px;
            height: 38px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 50%;
            background: rgba(14, 29, 56, 0.72);
            color: #ffffff;
            cursor: pointer;
            font-size: 18px;
        }

        .theme-toggle-moon {
            display: none;
        }

        [data-theme="dark"] .theme-toggle-sun {
            display: none;
        }

        [data-theme="dark"] .theme-toggle-moon {
            display: inline;
        }

        .login-left {
            width: 45%;

            background: rgba(0, 59, 123, 0.88);

            color: white;

            padding: 32px 45px 45px 45px;

            display: flex;
            flex-direction: column;
            justify-content: flex-start;

            backdrop-filter: blur(3px);
        }

        .logo-pln {
            margin-bottom: 32px;

            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
        }

        .logo-pln img {
            width: 220px;
            height: auto;

            display: block;

            object-fit: contain;
        }

        .login-left h2 {
            font-size: 25px;
            line-height: 1.3;

            margin-bottom: 15px;

            color: #ffffff;
        }

        .login-left p {
            font-size: 14px;
            line-height: 1.7;

            color: #e2e8f0;
        }

        .info-box {
            margin-top: 30px;

            padding: 15px;

            border-left: 4px solid #ffffff;

            background: rgba(255, 255, 255, 0.10);

            border-radius: 6px;

            font-size: 13px;
            line-height: 1.6;

            color: #ffffff;

            backdrop-filter: blur(3px);
        }

        .info-box strong {
            font-size: 14px;
            color: #ffffff;
        }

        .login-right {
            width: 55%;

            padding: 50px 55px;

            display: flex;
            flex-direction: column;
            justify-content: center;

            background: var(--login-surface);

            backdrop-filter: blur(5px);
        }

        .login-title {
            font-size: 30px;

            color: var(--login-heading);

            margin-bottom: 8px;
        }

        .login-subtitle {
            color: var(--login-muted);

            font-size: 14px;

            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;

            margin-bottom: 7px;

            font-size: 14px;

            font-weight: bold;

            color: var(--login-text);
        }

        .input-box {
            width: 100%;
            height: 46px;

            padding: 0 14px;

            border: 1px solid var(--login-border);

            border-radius: 7px;

            font-size: 14px;

            outline: none;

            background: var(--login-input);

            color: var(--login-text);

            transition: 0.2s;
        }

        .input-box:focus {
            border-color: #003b7b;

            background: var(--login-surface);

            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.18);
        }

        .input-box::placeholder {
            color: #94a3b8;
        }

        select.input-box {
            cursor: pointer;
            appearance: auto;
        }

        .error {
            background: #fee2e2;

            color: #b91c1c;

            border: 1px solid #fecaca;

            padding: 10px;

            border-radius: 7px;

            margin-bottom: 18px;

            font-size: 13px;
        }

        .btn-login {
            width: 100%;
            height: 48px;

            border: none;

            border-radius: 7px;

            background: #003b7b;

            color: #ffffff;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            margin-top: 5px;

            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background: #002f62;

            transform: translateY(-1px);

            box-shadow:
                0 5px 12px rgba(0, 59, 123, 0.30);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-dashboard {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 44px;
            border: 1px solid var(--login-border);
            border-radius: 7px;
            background: var(--login-input);
            color: var(--login-text);
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-dashboard:hover {
            border-color: #003b7b;
            background: rgba(0, 59, 123, 0.08);
            color: #003b7b;
            transform: translateY(-1px);
        }

        [data-theme="dark"] .btn-dashboard:hover {
            border-color: #4a9eff;
            background: rgba(74, 158, 255, 0.14);
            color: #ffffff;
        }

        .btn-dashboard:focus-visible {
            outline: 3px solid rgba(74, 158, 255, 0.3);
            outline-offset: 2px;
        }

        .footer-text {
            text-align: center;

            margin-top: 20px;

            color: #94a3b8;

            font-size: 11px;
        }

        @media (max-width: 900px) {
            .login-wrapper {
                width: 92%;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .login-wrapper {
                width: 100%;

                min-height: auto;

                flex-direction: column;

                border-radius: 15px;
            }

            .login-left,
            .login-right {
                width: 100%;
            }

            .login-left {
                padding: 30px 35px 40px 35px;
            }

            .logo-pln {
                margin-bottom: 25px;
            }

            .logo-pln img {
                width: 180px;
            }

            .login-left h2 {
                font-size: 22px;
            }

            .login-left p {
                font-size: 13px;
            }

            .info-box {
                width: 100%;
            }

            .login-right {
                padding: 40px 35px;
            }

            .login-title {
                font-size: 26px;
            }
        }

        @media (max-width: 480px) {
            .login-left {
                padding: 25px 25px 35px 25px;
            }

            .login-right {
                padding: 30px 25px;
            }

            .logo-pln img {
                width: 160px;
            }

            .login-title {
                font-size: 24px;
            }
        }
    </style>

</head>

<body>

    <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Aktifkan dark mode"
        title="Aktifkan dark mode">
        <span class="theme-toggle-sun" aria-hidden="true">☀</span>
        <span class="theme-toggle-moon" aria-hidden="true">☾</span>
    </button>

    <div class="login-wrapper">

        <div class="login-left">

            <div class="logo-pln">
                <img src="{{ asset('images/logo-pln-np.png') }}" alt="Logo PLN Nusantara Power">
            </div>

            <h2>
                SIGAP
            </h2>

            <p>
                Sistem Informasi Gangguan Andal Pembangkit sebagai sarana monitoring terpadu kesehatan unit dan
                equipment PLTA untuk mendukung keandalan operasional pembangkitan.
            </p>

            <div class="info-box">
                <strong>
                    Manajemen Pemeliharaan Aset
                </strong>

                <br>

                Kelola status operasi, keandalan alat, dan Work Order dalam satu platform terpadu.
            </div>

        </div>

        <div class="login-right">

            <h1 class="login-title">
                Selamat Datang
            </h1>

            <p class="login-subtitle">
                Silakan masuk ke akun SIGAP Anda
            </p>

            @if ($errors->any())

                <div class="error">
                    {{ $errors->first() }}
                </div>

            @endif

            <form action="{{ route('login.process') }}" method="POST">

                @csrf

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input type="email" id="email" name="email" class="input-box" placeholder="Masukkan email"
                        value="{{ old('email') }}" required>

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input type="password" id="password" name="password" class="input-box"
                        placeholder="Masukkan password" required>

                </div>

                <div class="form-group">

                    <label for="role">
                        Role
                    </label>

                    <select name="role" id="role" class="input-box" required>

                        <option value="" disabled selected>
                            Pilih Role
                        </option>

                        <option value="SO">
                            SO
                        </option>

                        <option value="CBM">
                            CBM
                        </option>

                    </select>

                </div>

                <button type="submit" class="btn-login">
                    MASUK
                </button>

            </form>

            <div class="footer-text">
                © {{ date('Y') }} SIGAP - Sistem Informasi Gangguan Andal Pembangkit
            </div>

            <div style="margin-top:16px;">
                <a href="{{ route('dashboard') }}" id="link-back-to-dashboard" class="btn-dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

        </div>

    </div>

    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const updateThemeToggle = function () {
            const isDark = document.documentElement.dataset.theme === 'dark';
            themeToggle.setAttribute('aria-label', isDark ? 'Aktifkan light mode' : 'Aktifkan dark mode');
            themeToggle.setAttribute('title', isDark ? 'Aktifkan light mode' : 'Aktifkan dark mode');
        };
        themeToggle.addEventListener('click', function () {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = nextTheme;
            localStorage.setItem('sigap-theme', nextTheme);
            updateThemeToggle();
        });
        updateThemeToggle();
    </script>

</body>

</html>