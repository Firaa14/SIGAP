<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIGAP - Sistem Informasi Monitoring Equipment PLTA | PLN Nusantara Power">
    <title>@yield('title', 'Dashboard') — SIGAP | PLN Nusantara Power</title>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('sigap-theme');
            document.documentElement.dataset.theme = savedTheme === 'dark' ? 'dark' : 'light';
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
    <style>
        /* ============================================================
           AUTH GATE MODAL
           ============================================================ */
        .auth-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(5, 15, 35, 0.62);
            backdrop-filter: blur(3px);
            z-index: 9000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.22s ease, visibility 0.22s ease;
        }
        .auth-modal-backdrop.is-open {
            opacity: 1;
            visibility: visible;
        }
        .auth-modal {
            background: var(--bg-surface, #FFFFFF);
            border: 1px solid var(--border-color, #E2E8F0);
            border-radius: 16px;
            padding: 36px 40px 32px;
            width: 420px;
            max-width: calc(100vw - 40px);
            box-shadow: 0 24px 60px rgba(5, 15, 35, 0.28), 0 8px 20px rgba(5, 15, 35, 0.12);
            transform: translateY(16px) scale(0.97);
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }
        .auth-modal-backdrop.is-open .auth-modal {
            transform: translateY(0) scale(1);
        }
        .auth-modal-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(0,102,204,0.12) 0%, rgba(0,59,123,0.08) 100%);
            border: 1px solid rgba(0,102,204,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .auth-modal-icon-wrap svg {
            color: var(--color-accent, #0066CC);
        }
        .auth-modal-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary, #172033);
            margin-bottom: 8px;
            letter-spacing: -0.2px;
        }
        .auth-modal-feature {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(0,102,204,0.08);
            color: var(--color-accent, #0066CC);
            border: 1px solid rgba(0,102,204,0.18);
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .auth-modal-desc {
            font-size: 13.5px;
            color: var(--text-secondary, #465268);
            line-height: 1.65;
            margin-bottom: 28px;
        }
        .auth-modal-actions {
            display: flex;
            gap: 10px;
        }
        .auth-modal-btn-primary {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            height: 42px;
            background: var(--color-accent, #0066CC);
            color: #FFFFFF;
            border: none;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.18s ease, transform 0.15s ease, box-shadow 0.18s ease;
        }
        .auth-modal-btn-primary:hover {
            background: var(--color-accent-hover, #0055AA);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0,102,204,0.3);
            color: #FFFFFF;
            text-decoration: none;
        }
        .auth-modal-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 42px;
            padding: 0 18px;
            background: var(--bg-surface-2, #F7F9FC);
            color: var(--text-secondary, #465268);
            border: 1px solid var(--border-color, #E2E8F0);
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.18s ease, border-color 0.18s ease;
        }
        .auth-modal-btn-secondary:hover {
            background: var(--bg-surface, #FFFFFF);
            border-color: #B0C4D8;
        }
        .auth-modal-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: none;
            border: none;
            color: var(--text-muted, #718096);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            line-height: 1;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .auth-modal-close:hover {
            background: var(--bg-surface-2, #F7F9FC);
            color: var(--text-primary, #172033);
        }
        [data-theme="dark"] .auth-modal {
            background: var(--bg-surface, #142746);
            border-color: var(--border-color, rgba(255,255,255,0.12));
        }
        [data-theme="dark"] .auth-modal-icon-wrap {
            background: rgba(74,158,255,0.12);
            border-color: rgba(74,158,255,0.2);
        }
        [data-theme="dark"] .auth-modal-feature {
            background: rgba(74,158,255,0.12);
            border-color: rgba(74,158,255,0.2);
        }
        [data-theme="dark"] .auth-modal-btn-secondary {
            background: var(--bg-surface-2, #193052);
        }
    </style>
</head>

<body>

    <div class="app-wrapper">

        {{-- Sidebar --}}
        <aside class="sidebar" role="navigation" aria-label="Navigasi Utama">

            {{-- Brand / Logo --}}
            <div class="sidebar-brand">
                @php
                    $logoPath = public_path('images/logo-pln-np.png');
                    $logoExists = file_exists($logoPath);
                @endphp

                @if($logoExists)
                    <img src="{{ asset('images/logo-pln-np.png') }}" alt="PLN" class="sidebar-logo">
                @else
                    <div class="sidebar-logo-placeholder" aria-label="PLN Logo">PLN</div>
                @endif
            </div>

            {{-- Navigation --}}
            <nav class="sidebar-nav">

                {{-- Menu Utama --}}
                <div class="sidebar-section-label">Menu</div>

                <a href="{{ route('dashboard') }}"
                    class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
                    <span class="nav-icon">⊞</span>
                    <span class="nav-label">Dashboard</span>
                </a>

                <a href="{{ route('upload.index') }}"
                    class="sidebar-nav-item {{ request()->routeIs('upload.*') ? 'active' : '' }}"
                    id="nav-upload"
                    @guest data-requires-auth="true" data-feature="Upload Data WO" @endguest>
                    <span class="nav-icon">↑</span>
                    <span class="nav-label">Upload Data WO</span>
                </a>

                <a href="{{ route('equipment.create') }}"
                    class="sidebar-nav-item {{ request()->routeIs('equipment.*') ? 'active' : '' }}"
                    id="nav-equipment-create"
                    @guest data-requires-auth="true" data-feature="Insert Equipment" @endguest>
                    <span class="nav-icon">＋</span>
                    <span class="nav-label">Insert Equipment</span>
                </a>

                <hr class="sidebar-divider">

                {{-- Daftar PLTA --}}
                <div class="sidebar-section-label">Data PLTA</div>

                @php
                    $pltaList = \App\Models\Plta::query()->orderBy('id')->get();
                @endphp

                @foreach ($pltaList as $plta)
                    <a href="{{ route('plta.show', $plta['slug']) }}"
                        class="sidebar-nav-item {{ request()->is('plta/' . $plta['slug']) ? 'active' : '' }}"
                        id="nav-plta-{{ $plta->slug }}" title="{{ $plta->nama_plta }} — {{ $plta->kode_prefix }}">
                        <span class="nav-icon" style="font-size:10px;">▸</span>
                        <span class="nav-label">{{ str_replace('PLTA ', '', $plta->nama_plta) }}</span>
                        <span class="plta-code">{{ $plta->kode_prefix }}</span>
                    </a>
                @endforeach

            </nav>

            {{-- Sidebar Footer --}}
            <div class="sidebar-footer">
                <div class="sidebar-footer-text">
                    © {{ date('Y') }} PLN Nusantara Power<br>
                    v1.0.0-beta &nbsp;·&nbsp; UI Prototype
                </div>
            </div>

        </aside>

        {{-- Main content --}}
        <div class="main-content">

            {{-- Top Bar / Header --}}
            <header class="topbar" role="banner">
                <div class="topbar-left">
                    <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                </div>
                <div class="topbar-right">
                    <div class="topbar-datetime" id="topbar-clock">
                        <div class="date" id="current-date"></div>
                        <div id="current-time"></div>
                    </div>

                    <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Aktifkan dark mode"
                        title="Aktifkan dark mode">
                        <span class="theme-toggle-icon theme-toggle-sun" aria-hidden="true">☀</span>
                        <span class="theme-toggle-icon theme-toggle-moon" aria-hidden="true">☾</span>
                    </button>

                    @auth
                        {{-- Role badge + Logout --}}
                        <span class="topbar-role-badge" title="Role aktif saat ini">
                            {{ session('login_role', auth()->user()->name) }}
                        </span>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                            @csrf
                            <button type="submit" class="btn-logout-icon" title="Logout">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                            </button>
                        </form>
                    @else
                        {{-- Tombol Login untuk guest --}}
                        <a href="{{ route('login') }}" class="btn-login-topbar" id="btn-login-topbar" title="Login">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                            Login
                        </a>
                    @endauth
                </div>
            </header>

            {{-- Page Content --}}
            <main class="page-content" role="main">
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Auth Gate Modal (hanya untuk guest) --}}
    @guest
    <div class="auth-modal-backdrop" id="auth-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
        <div class="auth-modal" id="auth-modal">
            <button type="button" class="auth-modal-close" id="auth-modal-close" aria-label="Tutup">&times;</button>
            <div class="auth-modal-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <div class="auth-modal-title" id="auth-modal-title">Login Diperlukan</div>
            <div class="auth-modal-feature" id="auth-modal-feature">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                <span id="auth-modal-feature-name">Fitur</span>
            </div>
            <p class="auth-modal-desc" id="auth-modal-desc">
                Fitur ini hanya dapat diakses oleh pengguna yang telah login sebagai <strong>SO</strong> atau <strong>CBM</strong>.
                Silakan masuk terlebih dahulu untuk melanjutkan.
            </p>
            <div class="auth-modal-actions">
                <a href="{{ route('login') }}" class="auth-modal-btn-primary" id="auth-modal-login-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    Login Sekarang
                </a>
                <button type="button" class="auth-modal-btn-secondary" id="auth-modal-cancel-btn">Batal</button>
            </div>
        </div>
    </div>
    @endguest

    <script>
        // Theme preference is shared across every authenticated page.
        const themeToggle = document.getElementById('theme-toggle');
        const updateThemeToggle = function () {
            const isDark = document.documentElement.dataset.theme === 'dark';
            if (!themeToggle) return;
            themeToggle.setAttribute('aria-label', isDark ? 'Aktifkan light mode' : 'Aktifkan dark mode');
            themeToggle.setAttribute('title', isDark ? 'Aktifkan light mode' : 'Aktifkan dark mode');
        };
        if (themeToggle) {
            themeToggle.addEventListener('click', function () {
                const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
                document.documentElement.dataset.theme = nextTheme;
                localStorage.setItem('sigap-theme', nextTheme);
                updateThemeToggle();
            });
            updateThemeToggle();
        }

        // Live clock
        function updateClock() {
            const now = new Date();
            const dateOptions = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const dateEl = document.getElementById('current-date');
            const timeEl = document.getElementById('current-time');
            if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', dateOptions);
            if (timeEl) timeEl.textContent = now.toLocaleTimeString('id-ID', timeOptions);

            // Elemen lain di halaman (mis. badge timestamp di atas peta) ikut mengikuti
            // jam yang sama, dipakai untuk keperluan screenshot/pelaporan.
            const fullOptions = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const liveClockEls = document.querySelectorAll('[data-live-clock]');
            liveClockEls.forEach(function (el) {
                el.textContent = now.toLocaleString('id-ID', fullOptions).replace(/\./g, ':').replace(',', ' ·');
            });
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Status Select dynamic styling
        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('status-select')) {
                const select = e.target;
                select.classList.remove('normal-select', 'abnormal-select', 'notready-select');
                const val = select.value;
                if (val === 'Normal') { select.classList.add('normal-select'); }
                if (val === 'Abnormal') { select.classList.add('abnormal-select'); }
                if (val === 'Not Ready') { select.classList.add('notready-select'); }
            }
        });

        // Auth Gate — intercept protected nav links untuk guest
        (function () {
            const backdrop  = document.getElementById('auth-modal-backdrop');
            if (!backdrop) return; // user sudah login, tidak perlu listener

            const closeBtn  = document.getElementById('auth-modal-close');
            const cancelBtn = document.getElementById('auth-modal-cancel-btn');
            const featureName = document.getElementById('auth-modal-feature-name');

            function openModal(featureLabel) {
                if (featureName) featureName.textContent = featureLabel;
                backdrop.classList.add('is-open');
                document.body.style.overflow = 'hidden';
                document.getElementById('auth-modal-close').focus();
            }

            function closeModal() {
                backdrop.classList.remove('is-open');
                document.body.style.overflow = '';
            }

            // Intercept klik pada semua elemen dengan data-requires-auth
            document.querySelectorAll('[data-requires-auth]').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    openModal(el.dataset.feature || 'Fitur Ini');
                });
            });

            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            // Tutup saat klik di luar modal
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) closeModal();
            });

            // Tutup dengan Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && backdrop.classList.contains('is-open')) closeModal();
            });
        })();
    </script>

    @yield('scripts')
</body>

</html>