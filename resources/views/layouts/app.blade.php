<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIGAP - Sistem Informasi Monitoring Equipment PLTA | PLN Nusantara Power">
    <title>@yield('title', 'Dashboard') — SIGAP | PLN Nusantara Power</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
                    class="sidebar-nav-item {{ request()->routeIs('upload.*') ? 'active' : '' }}" id="nav-upload">
                    <span class="nav-icon">↑</span>
                    <span class="nav-label">Upload Data WO</span>
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

                    {{-- Logout --}}
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
                </div>
            </header>

            {{-- Page Content --}}
            <main class="page-content" role="main">
                @yield('content')
            </main>

        </div>
    </div>

    <script>
        // Live clock
        function updateClock() {
            const now = new Date();
            const dateOptions = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const dateEl = document.getElementById('current-date');
            const timeEl = document.getElementById('current-time');
            if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', dateOptions);
            if (timeEl) timeEl.textContent = now.toLocaleTimeString('id-ID', timeOptions);
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
    </script>

    @yield('scripts')
</body>

</html>