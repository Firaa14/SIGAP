<!DOCTYPE html>
<html lang="id" data-theme="light" data-lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SIGAP</title>

    <script>
        (function () {
            const savedTheme = localStorage.getItem('sigap-theme');
            document.documentElement.dataset.theme = savedTheme === 'dark' ? 'dark' : 'light';
            const savedLang = localStorage.getItem('sigap-lang');
            document.documentElement.dataset.lang = (savedLang === 'en') ? 'en' : 'id';
            document.documentElement.lang = (savedLang === 'en') ? 'en' : 'id';
        })();
    </script>

    <style>
        :root {
            --login-surface: rgba(255, 255, 255, 0.70);
            --login-heading: #1e293b;
            --login-text: #334155;
            --login-muted: #64748b;
            --login-input: rgba(248, 250, 252, 0.95);
            --login-border: #cbd5e1;
        }

        [data-theme="dark"] {
            --login-surface: rgba(14, 29, 56, 0.70);
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

        /* Corner controls (theme + lang) */
        .corner-controls {
            position: fixed;
            top: 22px;
            right: 22px;
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .theme-toggle {
            width: 38px;
            height: 38px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 50%;
            background: rgba(14, 29, 56, 0.72);
            color: #ffffff;
            cursor: pointer;
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .lang-toggle {
            width: 38px;
            height: 38px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 50%;
            background: rgba(14, 29, 56, 0.72);
            color: #ffffff;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            font-family: inherit;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.18s ease, border-color 0.18s ease;
        }

        .lang-toggle:hover,
        .theme-toggle:hover {
            background: rgba(0, 59, 123, 0.88);
            border-color: rgba(255, 255, 255, 0.6);
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
            justify-content: space-between;

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
            flex: 1;
            height: 48px;

            border: none;

            border-radius: 7px;

            background: #003b7b;

            color: #ffffff;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            transition: all 0.2s ease;
        }

        .login-actions {
            display: flex;
            align-items: stretch;
            gap: 12px;
            margin-top: 5px;
        }

        .login-actions .btn-login,
        .login-actions .btn-dashboard {
            flex: 1;
            height: 48px;
            box-sizing: border-box;
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
            height: 48px;
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

            margin-top: 16px;

            color: #6c7582;

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

            .login-actions {
                flex-direction: column;
            }

            .login-actions .btn-dashboard {
                height: 48px;
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

    <div class="corner-controls">
        <button type="button" class="lang-toggle" id="lang-toggle" aria-label="Switch to English"
            title="Switch to English">
            <span id="lang-toggle-label">EN</span>
        </button>
        <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Aktifkan dark mode"
            title="Aktifkan dark mode">
            <span class="theme-toggle-sun" aria-hidden="true">☀</span>
            <span class="theme-toggle-moon" aria-hidden="true">☾</span>
        </button>
    </div>

    <div class="login-wrapper">

        <div class="login-left">

            <div class="logo-pln">
                <img src="{{ asset('images/logo-pln-np.png') }}" alt="Logo PLN Nusantara Power">
            </div>

            <h2>
                SIGAP
            </h2>

            <p id="login-left-desc">
                Sistem Informasi Gangguan Andal Pembangkit sebagai sarana monitoring terpadu kesehatan unit dan
                equipment PLTA untuk mendukung keandalan operasional pembangkitan.
            </p>

            <div class="info-box">
                <strong id="info-box-title">
                    Manajemen Pemeliharaan Aset
                </strong>

                <br>

                <span id="info-box-desc">Kelola status operasi, keandalan alat, dan Work Order dalam satu platform
                    terpadu.</span>
            </div>

        </div>

        <div class="login-right">

            <h1 class="login-title" id="login-title">
                Selamat Datang
            </h1>

            <p class="login-subtitle" id="login-subtitle">
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

                    <label for="email" id="label-email">
                        Email
                    </label>

                    <input type="email" id="email" name="email" class="input-box" placeholder="Masukkan email"
                        data-placeholder-id="Masukkan email" data-placeholder-en="Enter your email"
                        value="{{ old('email') }}" required>

                </div>

                <div class="form-group">

                    <label for="password" id="label-password">
                        Password
                    </label>

                    <input type="password" id="password" name="password" class="input-box"
                        placeholder="Masukkan password" data-placeholder-id="Masukkan password"
                        data-placeholder-en="Enter your password" required>

                </div>

                <div class="form-group">

                    <label for="role" id="label-role">
                        Role
                    </label>

                    <select name="role" id="role" class="input-box" required>

                        <option value="" disabled selected id="opt-select-role">
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

                <div class="login-actions">
                    <button type="submit" class="btn-login" id="btn-submit-login">
                        MASUK
                    </button>

                    <a href="{{ route('dashboard') }}" id="link-back-to-dashboard" class="btn-dashboard">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span id="btn-back-text">Kembali ke Dashboard</span>
                    </a>
                </div>

            </form>

            <div class="footer-text" id="login-footer">
                © {{ date('Y') }} SIGAP - PLN Nusantara Power Unit Pmebangkitan Brantas
            </div>

        </div>

    </div>

    <script>
        // ============================================================
        // LOGIN PAGE I18N (standalone — tidak extend layouts.app)
        // ============================================================
        var LOGIN_I18N = {
            id: {
                lang_switch: 'Ganti ke Bahasa Inggris',
                title: 'Selamat Datang',
                subtitle: 'Silakan masuk ke akun SIGAP Anda',
                label_email: 'Email',
                label_password: 'Password',
                label_role: 'Role',
                opt_select_role: 'Pilih Role',
                btn_submit: 'MASUK',
                btn_back: 'Kembali ke Dashboard',
                footer: '© ' + new Date().getFullYear() + ' SIGAP - PLN Nusantara Power Unit Pembangkitan Brantas',
                left_desc: 'Sistem Informasi Gangguan Andal Pembangkit sebagai sarana monitoring terpadu kesehatan unit dan equipment PLTA untuk mendukung keandalan operasional pembangkitan.',
                info_title: 'Manajemen Pemeliharaan Aset',
                info_desc: 'Kelola status operasi, keandalan alat, dan Work Order dalam satu platform terpadu.',
                placeholder_email: 'Masukkan email',
                placeholder_pass: 'Masukkan password',
                theme_dark: 'Aktifkan dark mode',
                theme_light: 'Aktifkan light mode',
            },
            en: {
                lang_switch: 'Switch to Indonesian',
                title: 'Welcome',
                subtitle: 'Please sign in to your SIGAP account',
                label_email: 'Email',
                label_password: 'Password',
                label_role: 'Role',
                opt_select_role: 'Select Role',
                btn_submit: 'SIGN IN',
                btn_back: 'Back to Dashboard',
                footer: '© ' + new Date().getFullYear() + ' SIGAP - PLN Nusantara Power Unit Pembangkitan Brantas',
                left_desc: 'Reliable Generator Disruption Information System — an integrated monitoring platform for PLTA unit and equipment health to support operational reliability.',
                info_title: 'Asset Maintenance Management',
                info_desc: 'Manage operating status, equipment reliability, and Work Orders in one integrated platform.',
                placeholder_email: 'Enter your email',
                placeholder_pass: 'Enter your password',
                theme_dark: 'Enable dark mode',
                theme_light: 'Enable light mode',
            },
        };

        function getLoginLang() {
            return localStorage.getItem('sigap-lang') === 'en' ? 'en' : 'id';
        }

        function applyLoginLang(lang) {
            document.documentElement.dataset.lang = lang;
            document.documentElement.lang = lang;

            var d = LOGIN_I18N[lang];

            // Toggle button
            var toggleLabel = document.getElementById('lang-toggle-label');
            var toggleBtn = document.getElementById('lang-toggle');
            if (toggleLabel) { toggleLabel.textContent = lang === 'en' ? 'ID' : 'EN'; }
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-label', d.lang_switch);
                toggleBtn.setAttribute('title', d.lang_switch);
            }

            // Right panel
            var titleEl = document.getElementById('login-title');
            var subtitleEl = document.getElementById('login-subtitle');
            var submitBtn = document.getElementById('btn-submit-login');
            var backText = document.getElementById('btn-back-text');
            var footerEl = document.getElementById('login-footer');
            var labelEmail = document.getElementById('label-email');
            var labelPass = document.getElementById('label-password');
            var labelRole = document.getElementById('label-role');
            var optRole = document.getElementById('opt-select-role');
            var emailInput = document.getElementById('email');
            var passInput = document.getElementById('password');

            if (titleEl) { titleEl.textContent = d.title; }
            if (subtitleEl) { subtitleEl.textContent = d.subtitle; }
            if (submitBtn) { submitBtn.textContent = d.btn_submit; }
            if (backText) { backText.textContent = d.btn_back; }
            if (footerEl) { footerEl.textContent = d.footer; }
            if (labelEmail) { labelEmail.textContent = d.label_email; }
            if (labelPass) { labelPass.textContent = d.label_password; }
            if (labelRole) { labelRole.textContent = d.label_role; }
            if (optRole) { optRole.textContent = d.opt_select_role; }
            if (emailInput) { emailInput.placeholder = d.placeholder_email; }
            if (passInput) { passInput.placeholder = d.placeholder_pass; }

            // Left panel
            var leftDesc = document.getElementById('login-left-desc');
            var infoTitle = document.getElementById('info-box-title');
            var infoDesc = document.getElementById('info-box-desc');
            if (leftDesc) { leftDesc.textContent = d.left_desc; }
            if (infoTitle) { infoTitle.textContent = d.info_title; }
            if (infoDesc) { infoDesc.textContent = d.info_desc; }

            // Theme toggle labels
            updateThemeToggleLabel(lang);
        }

        function updateThemeToggleLabel(lang) {
            var isDark = document.documentElement.dataset.theme === 'dark';
            var d = LOGIN_I18N[lang];
            var themeBtn = document.getElementById('theme-toggle');
            if (!themeBtn) { return; }
            themeBtn.setAttribute('aria-label', isDark ? d.theme_light : d.theme_dark);
            themeBtn.setAttribute('title', isDark ? d.theme_light : d.theme_dark);
        }

        // Init
        applyLoginLang(getLoginLang());

        // Lang toggle click
        var langToggle = document.getElementById('lang-toggle');
        if (langToggle) {
            langToggle.addEventListener('click', function () {
                var next = getLoginLang() === 'en' ? 'id' : 'en';
                localStorage.setItem('sigap-lang', next);
                applyLoginLang(next);
            });
        }

        // Theme toggle click
        var themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function () {
                var nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
                document.documentElement.dataset.theme = nextTheme;
                localStorage.setItem('sigap-theme', nextTheme);
                updateThemeToggleLabel(getLoginLang());
            });
        }
    </script>

</body>

</html>