<!DOCTYPE html>
<html lang="id" data-theme="light" data-lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIGAP - Sistem Informasi Monitoring Equipment PLTA | PLN Nusantara Power">
    <title>@yield('title', 'Dashboard') — SIGAP | PLN Nusantara Power</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function () {
            const savedTheme = localStorage.getItem('sigap-theme');
            document.documentElement.dataset.theme = savedTheme === 'dark' ? 'dark' : 'light';
            const savedLang = localStorage.getItem('sigap-lang');
            document.documentElement.dataset.lang = (savedLang === 'en') ? 'en' : 'id';
            document.documentElement.lang = (savedLang === 'en') ? 'en' : 'id';
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

        /* ============================================================
           LANGUAGE TOGGLE
           ============================================================ */
        .lang-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid var(--border-color, #E2E8F0);
            background: var(--bg-surface-2, #F7F9FC);
            color: var(--text-secondary, #465268);
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            font-family: inherit;
            letter-spacing: 0.3px;
            transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.15s ease;
            flex-shrink: 0;
        }
        .lang-toggle:hover {
            background: var(--color-accent, #0066CC);
            border-color: var(--color-accent, #0066CC);
            color: #FFFFFF;
            transform: scale(1.08);
        }
        [data-theme="dark"] .lang-toggle {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.14);
            color: var(--text-secondary, #A8BFDA);
        }
        [data-theme="dark"] .lang-toggle:hover {
            background: var(--color-accent, #4A9EFF);
            border-color: var(--color-accent, #4A9EFF);
            color: #FFFFFF;
        }
    </style>
</head>

<body>

    <div class="app-wrapper">

        {{-- Sidebar --}}
        <aside class="sidebar" role="navigation" aria-label="Main Navigation" data-i18n-aria="aria_main_navigation">

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
                <div class="sidebar-section-label" data-i18n="nav_menu">Menu</div>

                <a href="{{ route('dashboard') }}"
                    class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
                    <span class="nav-icon">⊞</span>
                    <span class="nav-label" data-i18n="nav_dashboard">Dashboard</span>
                </a>

                <a href="{{ route('upload.index') }}"
                    class="sidebar-nav-item {{ request()->routeIs('upload.*') ? 'active' : '' }}"
                    id="nav-upload"
                    @guest data-requires-auth="true" data-feature-id="Upload Data WO" data-feature="Upload Data WO" @endguest>
                    <span class="nav-icon">↑</span>
                    <span class="nav-label" data-i18n="nav_upload_wo">Upload Data WO</span>
                </a>

                <a href="{{ route('equipment.create') }}"
                    class="sidebar-nav-item {{ request()->routeIs('equipment.*') ? 'active' : '' }}"
                    id="nav-equipment-create"
                    @guest data-requires-auth="true" data-feature-id="Insert Equipment" data-feature="Insert Equipment" @endguest>
                    <span class="nav-icon">＋</span>
                    <span class="nav-label" data-i18n="nav_insert_equipment">Insert Equipment</span>
                </a>

                <hr class="sidebar-divider">

                {{-- Daftar PLTA --}}
                <div class="sidebar-section-label" data-i18n="nav_data_plta">Data PLTA</div>

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
                    v1.0.0-beta &nbsp;·&nbsp; <span data-i18n="sidebar_ui_prototype">UI Prototype</span>
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

                    {{-- Language Toggle --}}
                    <button type="button" class="lang-toggle" id="lang-toggle" aria-label="Switch to English" title="Switch to English">
                        <span id="lang-toggle-label">EN</span>
                    </button>

                    <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Enable dark mode"
                        title="Enable dark mode">
                        <span class="theme-toggle-icon theme-toggle-sun" aria-hidden="true">☀</span>
                        <span class="theme-toggle-icon theme-toggle-moon" aria-hidden="true">☾</span>
                    </button>

                    @auth
                        {{-- Role badge + Logout --}}
                        <span class="topbar-role-badge" data-i18n-title="active_role" title="Current active role">
                            {{ session('login_role', auth()->user()->name) }}
                        </span>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                            @csrf
                            <button type="submit" class="btn-logout-icon" id="btn-logout" data-i18n-title="btn_logout" title="Logout">
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
                        <a href="{{ route('login') }}" class="btn-login-topbar" id="btn-login-topbar" data-i18n-title="btn_login" title="Login">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                            <span data-i18n="btn_login">Login</span>
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
            <button type="button" class="auth-modal-close" id="auth-modal-close" data-i18n-aria="modal_close" aria-label="Close">&times;</button>
            <div class="auth-modal-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <div class="auth-modal-title" id="auth-modal-title" data-i18n="modal_login_required">Login Diperlukan</div>
            <div class="auth-modal-feature" id="auth-modal-feature">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                <span id="auth-modal-feature-name" data-i18n="feature_label">Feature</span>
            </div>
            <p class="auth-modal-desc" id="auth-modal-desc" data-i18n="modal_login_desc">
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
                    <span data-i18n="modal_login_btn">Login Sekarang</span>
                </a>
                <button type="button" class="auth-modal-btn-secondary" id="auth-modal-cancel-btn" data-i18n="modal_cancel_btn">Batal</button>
            </div>
        </div>
    </div>
    @endguest

    <script>
        // ============================================================
        // I18N DICTIONARY
        // ============================================================
        window.SIGAP_I18N = {
            id: {
                // Navigation
                nav_menu: 'Menu',
                nav_dashboard: 'Dashboard',
                nav_upload_wo: 'Upload Data WO',
                nav_insert_equipment: 'Insert Equipment',
                nav_data_plta: 'Data PLTA',
                sidebar_ui_prototype: 'UI Prototype',
                aria_main_navigation: 'Navigasi Utama',

                // Topbar
                btn_login: 'Login',
                btn_logout: 'Logout',
                active_role: 'Role aktif saat ini',
                feature_label: 'Fitur',
                theme_dark: 'Aktifkan dark mode',
                theme_light: 'Aktifkan light mode',
                lang_switch: 'Ganti ke Bahasa Inggris',

                // Auth Modal
                modal_close: 'Tutup',
                modal_login_required: 'Login Diperlukan',
                modal_login_desc: 'Fitur ini hanya dapat diakses oleh pengguna yang telah login sebagai <strong>SO</strong> atau <strong>CBM</strong>. Silakan masuk terlebih dahulu untuk melanjutkan.',
                modal_login_btn: 'Login Sekarang',
                modal_cancel_btn: 'Batal',

                // Dashboard
                dash_total_plta: 'Total PLTA',
                dash_total_equipment: 'Total Equipment',
                dash_status_normal: 'Status Normal',
                dash_status_abnormal: 'Status Abnormal',
                dash_status_not_ready: 'Status Not Ready',
                status_normal: 'Normal',
                status_abnormal: 'Abnormal',
                status_not_ready: 'Not Ready',
                dash_map_aria: 'Peta sebaran lokasi 13 PLTA UP Brantas',
                status_equipment_normal: 'Equipment Normal',
                status_equipment_abnormal: 'Equipment Abnormal',
                status_no_data: 'Tidak ada data',
                status_no_equipment: 'Belum ada equipment dengan status',
                status_abnormal_heading: 'Status ABNORMAL',
                status_normal_heading: 'Status NORMAL',
                status_not_ready_heading: 'Status NOT READY',
                dash_map_title: 'Peta Sebaran PLTA UP Brantas',
                dash_show_labels: 'Tampilkan Semua Label',
                dash_hide_labels: 'Sembunyikan Label',
                dash_activity_title: 'Aktivitas Status Terbaru',
                dash_update: 'Update:',
                dash_th_upload_time: 'Waktu Upload',
                dash_th_plta: 'PLTA',
                dash_th_assetnum: 'ASSETNUM',
                dash_th_status: 'Status',
                    dash_th_wo: 'No WO',
                dash_th_report_date: 'Tanggal Report',
                dash_th_duration: 'Durasi',
                dash_empty_title: 'Belum ada aktivitas',
                dash_empty_text: 'Upload file Excel WO untuk melihat aktivitas terbaru.',

                // PLTA Show
                plta_capacity: 'Kapasitas:',
                plta_equipment_registered: 'Equipment Terdaftar',
                plta_search_label: 'Cari:',
                plta_search_placeholder: 'Cari equipment, KKS, ASSETNUM...',
                plta_unit_label: 'Unit:',
                plta_all_units: 'Semua Unit',
                plta_status_label: 'Status:',
                plta_all_status: 'Semua Status',
                plta_reset_filter: '↺ Reset Filter',
                plta_table_title: 'Daftar Equipment —',
                plta_equipment_shown: 'equipment ditampilkan',
                plta_th_no: 'No',
                plta_th_unit: 'Unit',
                plta_th_system: 'System',
                plta_th_equipment: 'Equipment',
                plta_th_kks: 'KKS',
                plta_th_assetnum: 'ASSETNUM',
                plta_th_op_status: 'Status Operasi',
                plta_th_notes: 'Deskripsi',
                plta_th_report_date: 'Tanggal Report',
                plta_th_duration: 'Durasi',
                plta_no_data_title: 'Tidak ada data yang cocok',
                plta_no_data_text: 'Coba ubah kata kunci pencarian atau filter.',
                plta_empty_title: 'Belum ada data equipment',
                plta_empty_text: 'Data equipment untuk',
                plta_empty_text2: 'belum tersedia.',
                plta_back_dashboard: '← Kembali ke Dashboard',
                plta_prev: '← PLTA Sebelumnya',
                plta_next: 'PLTA Berikutnya →',
                plta_toast_success: 'berhasil disimpan:',
                plta_toast_error: 'Gagal menyimpan status.',
                map_view_equipment_details: 'Lihat Detail Equipment →',

                // Upload Index
                upload_flash_fail: 'Gagal Memproses File',
                upload_instr_title: 'ℹ \u00a0Instruksi Wajib Sebelum Upload',
                upload_instr_1: 'File Excel yang di-upload <strong>harus sudah difilter secara manual</strong> oleh user terlebih dahulu.',
                upload_instr_2: 'Pastikan file hanya mengandung data WO dengan Worktype yang diizinkan:',
                upload_instr_3: 'Hapus semua baris dengan Worktype selain keempat di atas sebelum upload.',
                upload_instr_4: 'Pastikan file Excel memiliki kolom: <strong>NO WO, DESCRIPTION, WORKTYPE, STATUS, ASSETNUM</strong>.',
                upload_instr_5: 'ASSETNUM harus sesuai dengan data master PLTA yang terdaftar dalam sistem.',
                upload_warning_title: 'Perhatian',
                upload_warning_text: 'Data yang di-upload akan digunakan sebagai dasar penentuan Status Operasi Equipment secara otomatis. WO dengan status <strong>APPR / INPRG / PTWCL / PTWR / WPTW</strong> akan menetapkan status <strong>ABNORMAL</strong>. WO dengan status <strong>CLOSE / COMP</strong> akan menetapkan status <strong>NORMAL</strong>.',
                upload_card_title: 'Pilih File Excel',
                upload_dropzone_title: 'Klik atau seret file ke sini',
                upload_dropzone_subtitle: 'Format yang diterima: .xlsx, .xls',
                upload_dropzone_btn: 'Pilih File',
                upload_cancel: '✕ Batalkan',
                upload_preview_btn: '↑ Tampilkan Preview',
                upload_loading: '⏳ Membaca file...',
                upload_col_ref_title: 'Kolom yang Diperlukan',
                upload_col_name: 'Nama Kolom',
                upload_col_desc_label: 'Deskripsi',
                upload_col_required: 'Wajib',
                upload_status_rules_title: 'Aturan Penentuan Status Operasi',
                upload_preview_title: 'Preview Data Excel',
                upload_valid_rows: 'baris valid',
                upload_validation_title: '✓ Hasil Validasi File',
                upload_total_rows: 'Total Baris',
                upload_valid: 'Valid',
                upload_invalid: 'Tidak Valid',
                upload_new_data: 'Data Baru',
                upload_update_data: 'Data Diperbarui',
                upload_plta_detected: 'PLTA terdeteksi:',
                upload_error_rows: 'baris tidak dapat diproses',
                upload_row_prefix: 'Baris',
                upload_no_valid: 'Tidak ada data valid untuk ditampilkan.',
                upload_cancel_upload: '✕ Batalkan Upload',
                upload_confirm: '✓ Konfirmasi & Import Data',
                upload_rows_suffix: 'baris',
                upload_no_valid_import: 'Tidak ada data valid untuk diimport.',
                upload_overlay_title: 'Sedang Memproses Import…',
                upload_overlay_msg: 'Mohon tunggu, data Work Order sedang diproses dan didistribusikan ke seluruh PLTA.',
                upload_overlay_warning: 'Jangan tutup atau me-refresh halaman ini.',
                upload_confirm_dialog: 'Apakah Anda yakin ingin mengimport data ini ke database?\n\nProses ini tidak dapat dibatalkan setelah dikonfirmasi.',
                upload_processing: 'Memproses',
                upload_processing_suffix: 'baris data Work Order…',
                upload_col_no_wo_desc: 'Nomor Work Order unik',
                upload_col_desc_desc: 'Deskripsi pekerjaan WO',
                upload_col_worktype_desc: 'CM / EJ / EV / PAM',
                upload_col_status_desc: 'APPR / INPRG / CLOSE / COMP / dll',
                upload_col_assetnum_desc: 'Identifier equipment (contoh: BSGR010078)',
                upload_col_namaasset_desc: 'Nama equipment (opsional, diambil dari master jika kosong)',
                upload_col_reportdate_desc: 'Tanggal laporan WO (opsional)',
                upload_distrib_title: 'Distribusi PLTA via ASSETNUM',
                upload_distrib_desc: '4 karakter pertama ASSETNUM menentukan PLTA tujuan.',
                upload_distrib_example: 'Contoh:',
                upload_distrib_arrow: '→ PLTA',
                upload_th_preview_no: '#',
                upload_th_assetnum: 'ASSETNUM',
                upload_th_asset_name: 'Nama Asset',
                    upload_th_no_wo: 'No WO',
                upload_th_description: 'Description',
                upload_th_worktype: 'Worktype',
                upload_th_wo_status: 'Status WO',
                upload_th_plta: 'PLTA',
                upload_th_op_status: 'Status Operasi',
                upload_th_action: 'Aksi',

                // Upload Result
                result_success_title: 'Data Work Order Berhasil Diimport',
                result_success_desc: 'telah diproses dan data tersebar ke seluruh PLTA yang relevan.',
                result_upload_again: '↑ Upload Lagi',
                result_view_dashboard: '⊞ Lihat Dashboard',
                result_summary_title: 'Ringkasan Import',
                result_uploaded_at: 'Diupload',
                result_done_badge: 'SELESAI',
                result_total_rows: 'Total Baris',
                result_success_rows: 'Berhasil Diproses',
                result_failed_rows: 'Gagal Validasi',
                result_new_rows: 'Data Baru (INSERT)',
                result_updated_rows: 'Data Diperbarui',
                result_note_label: 'Catatan:',
                result_note_text: 'Status operasi equipment telah diperbarui secara otomatis berdasarkan data WO yang diimport.',
                result_distrib_title: 'Distribusi Data ke PLTA',
                result_distrib_affected: 'dari',
                result_distrib_affected2: 'PLTA terdampak',
                result_no_distrib: 'Tidak ada distribusi PLTA tercatat.',
                result_th_plta: 'PLTA',
                result_th_wo_count: 'Jumlah WO',
                result_upload_another: '↑ Upload File Lain',
                result_go_dashboard: '⊞ Lihat Dashboard PLTA',

                // Equipment Create
                equip_save_btn: 'Simpan Equipment',
                equip_cancel_btn: 'Batal',
                equip_select_plta: 'Pilih PLTA',
            },

            en: {
                // Navigation
                nav_menu: 'Menu',
                nav_dashboard: 'Dashboard',
                nav_upload_wo: 'Upload WO Data',
                nav_insert_equipment: 'Insert Equipment',
                nav_data_plta: 'PLTA Data',
                sidebar_ui_prototype: 'UI Prototype',
                aria_main_navigation: 'Main Navigation',

                // Topbar
                btn_login: 'Login',
                btn_logout: 'Logout',
                active_role: 'Current active role',
                feature_label: 'Feature',
                theme_dark: 'Enable dark mode',
                theme_light: 'Enable light mode',
                lang_switch: 'Switch to Indonesian',

                // Auth Modal
                modal_close: 'Close',
                modal_login_required: 'Login Required',
                modal_login_desc: 'This feature is only accessible to users logged in as <strong>SO</strong> or <strong>CBM</strong>. Please sign in to continue.',
                modal_login_btn: 'Login Now',
                modal_cancel_btn: 'Cancel',

                // Dashboard
                dash_total_plta: 'Total PLTA',
                dash_total_equipment: 'Total Equipment',
                dash_status_normal: 'Normal Status',
                dash_status_abnormal: 'Abnormal Status',
                dash_status_not_ready: 'Not Ready Status',
                status_normal: 'Normal',
                status_abnormal: 'Abnormal',
                status_not_ready: 'Not Ready',
                dash_map_aria: 'Distribution map of 13 PLTA UP Brantas locations',
                status_equipment_normal: 'Normal Equipment',
                status_equipment_abnormal: 'Abnormal Equipment',
                status_no_data: 'No data available',
                status_no_equipment: 'No equipment with status',
                status_abnormal_heading: 'ABNORMAL Status',
                status_normal_heading: 'NORMAL Status',
                status_not_ready_heading: 'NOT READY Status',
                dash_map_title: 'PLTA UP Brantas Distribution Map',
                dash_show_labels: 'Show All Labels',
                dash_hide_labels: 'Hide Labels',
                dash_activity_title: 'Latest Status Activity',
                dash_update: 'Updated:',
                dash_th_upload_time: 'Upload Time',
                dash_th_plta: 'PLTA',
                dash_th_assetnum: 'ASSETNUM',
                dash_th_status: 'Status',
                dash_th_wo: 'No WO',
                dash_th_report_date: 'Report Date',
                dash_th_duration: 'Duration',
                dash_empty_title: 'No activity yet',
                dash_empty_text: 'Upload a WO Excel file to see the latest activity.',

                // PLTA Show
                plta_capacity: 'Capacity:',
                plta_equipment_registered: 'Equipment Registered',
                plta_search_label: 'Search:',
                plta_search_placeholder: 'Search equipment, KKS, ASSETNUM...',
                plta_unit_label: 'Unit:',
                plta_all_units: 'All Units',
                plta_status_label: 'Status:',
                plta_all_status: 'All Statuses',
                plta_reset_filter: '↺ Reset Filter',
                plta_table_title: 'Equipment List —',
                plta_equipment_shown: 'equipment displayed',
                plta_th_no: 'No',
                plta_th_unit: 'Unit',
                plta_th_system: 'System',
                plta_th_equipment: 'Equipment',
                plta_th_kks: 'KKS',
                plta_th_assetnum: 'ASSETNUM',
                plta_th_op_status: 'Operating Status',
                plta_th_notes: 'Notes',
                plta_th_report_date: 'Report Date',
                plta_th_duration: 'Duration',
                plta_no_data_title: 'No matching data',
                plta_no_data_text: 'Try changing the search keyword or filter.',
                plta_empty_title: 'No equipment data yet',
                plta_empty_text: 'Equipment data for',
                plta_empty_text2: 'is not yet available.',
                plta_back_dashboard: '← Back to Dashboard',
                plta_prev: '← Previous PLTA',
                plta_next: 'Next PLTA →',
                plta_toast_success: 'saved successfully:',
                plta_toast_error: 'Failed to save status.',

                // Upload Index
                upload_flash_fail: 'Failed to Process File',
                upload_instr_title: 'ℹ \u00a0Mandatory Instructions Before Upload',
                upload_instr_1: 'The Excel file to be uploaded <strong>must be manually filtered</strong> by the user first.',
                upload_instr_2: 'Ensure the file only contains WO data with permitted Worktypes:',
                upload_instr_3: 'Remove all rows with Worktypes other than the four above before uploading.',
                upload_instr_4: 'Ensure the Excel file has the columns: <strong>NO WO, DESCRIPTION, WORKTYPE, STATUS, ASSETNUM</strong>.',
                upload_instr_5: 'ASSETNUM must match the PLTA master data registered in the system.',
                upload_warning_title: 'Attention',
                upload_warning_text: 'The uploaded data will be used as the basis for automatically determining Equipment Operating Status. WOs with status <strong>APPR / INPRG / PTWCL / PTWR / WPTW</strong> will set status to <strong>ABNORMAL</strong>. WOs with status <strong>CLOSE / COMP</strong> will set status to <strong>NORMAL</strong>.',
                upload_card_title: 'Select Excel File',
                upload_dropzone_title: 'Click or drag file here',
                upload_dropzone_subtitle: 'Accepted formats: .xlsx, .xls',
                upload_dropzone_btn: 'Choose File',
                upload_cancel: '✕ Cancel',
                upload_preview_btn: '↑ Show Preview',
                upload_loading: '⏳ Reading file...',
                upload_col_ref_title: 'Required Columns',
                upload_col_name: 'Column Name',
                upload_col_desc_label: 'Description',
                upload_col_required: 'Required',
                upload_status_rules_title: 'Operating Status Rules',
                upload_preview_title: 'Excel Data Preview',
                upload_valid_rows: 'valid rows',
                upload_validation_title: '✓ File Validation Results',
                upload_total_rows: 'Total Rows',
                upload_valid: 'Valid',
                upload_invalid: 'Invalid',
                upload_new_data: 'New Data',
                upload_update_data: 'Updated Data',
                upload_plta_detected: 'Detected PLTA:',
                upload_error_rows: 'rows could not be processed',
                upload_row_prefix: 'Row',
                upload_no_valid: 'No valid data to display.',
                upload_cancel_upload: '✕ Cancel Upload',
                upload_confirm: '✓ Confirm & Import Data',
                upload_rows_suffix: 'rows',
                upload_no_valid_import: 'No valid data to import.',
                upload_overlay_title: 'Processing Import…',
                upload_overlay_msg: 'Please wait, Work Order data is being processed and distributed to all PLTAs.',
                upload_overlay_warning: 'Do not close or refresh this page.',
                upload_confirm_dialog: 'Are you sure you want to import this data into the database?\n\nThis process cannot be undone after confirmation.',
                upload_processing: 'Processing',
                upload_processing_suffix: 'Work Order data rows…',
                upload_col_no_wo_desc: 'Unique Work Order number',
                upload_col_desc_desc: 'WO work description',
                upload_col_worktype_desc: 'CM / EJ / EV / PAM',
                upload_col_status_desc: 'APPR / INPRG / CLOSE / COMP / etc.',
                upload_col_assetnum_desc: 'Equipment identifier (e.g. BSGR010078)',
                upload_col_namaasset_desc: 'Equipment name (optional, taken from master if empty)',
                upload_col_reportdate_desc: 'WO report date (optional)',
                upload_distrib_title: 'PLTA Distribution via ASSETNUM',
                upload_distrib_desc: 'The first 4 characters of ASSETNUM determine the target PLTA.',
                upload_distrib_example: 'Example:',
                upload_distrib_arrow: '→ PLTA',
                upload_th_preview_no: '#',
                upload_th_assetnum: 'ASSETNUM',
                upload_th_asset_name: 'Asset Name',
                upload_th_no_wo: 'No WO',
                upload_th_description: 'Description',
                upload_th_worktype: 'Worktype',
                upload_th_wo_status: 'WO Status',
                upload_th_plta: 'PLTA',
                upload_th_op_status: 'Operating Status',
                upload_th_action: 'Action',

                // Upload Result
                result_success_title: 'Work Order Data Successfully Imported',
                result_success_desc: 'has been processed and data has been distributed to all relevant PLTAs.',
                result_upload_again: '↑ Upload Again',
                result_view_dashboard: '⊞ View Dashboard',
                result_summary_title: 'Import Summary',
                result_uploaded_at: 'Uploaded',
                result_done_badge: 'DONE',
                result_total_rows: 'Total Rows',
                result_success_rows: 'Successfully Processed',
                result_failed_rows: 'Validation Failed',
                result_new_rows: 'New Data (INSERT)',
                result_updated_rows: 'Updated Data',
                result_note_label: 'Note:',
                result_note_text: 'Equipment operating status has been automatically updated based on the imported WO data.',
                result_distrib_title: 'Data Distribution to PLTA',
                result_distrib_affected: 'of',
                result_distrib_affected2: 'PLTAs affected',
                result_no_distrib: 'No PLTA distribution recorded.',
                result_th_plta: 'PLTA',
                result_th_wo_count: 'WO Count',
                result_upload_another: '↑ Upload Another File',
                result_go_dashboard: '⊞ View PLTA Dashboard',

                // Equipment Create
                equip_save_btn: 'Save Equipment',
                equip_cancel_btn: 'Cancel',
                equip_select_plta: 'Select PLTA',
            },
        };

        // ============================================================
        // I18N ENGINE
        // ============================================================
        (function () {
            function getLang() {
                return localStorage.getItem('sigap-lang') === 'en' ? 'en' : 'id';
            }

            function t(key) {
                const lang = getLang();
                return (window.SIGAP_I18N[lang] && window.SIGAP_I18N[lang][key]) || key;
            }

            window.SIGAP_T = t;

            function applyLang(lang) {
                // Update html attributes
                document.documentElement.dataset.lang = lang;
                document.documentElement.lang = lang;

                // Update label toggle
                const toggleLabel = document.getElementById('lang-toggle-label');
                const toggleBtn   = document.getElementById('lang-toggle');
                if (toggleLabel) { toggleLabel.textContent = lang === 'en' ? 'ID' : 'EN'; }
                if (toggleBtn) {
                    const switchLabel = lang === 'en' ? t('lang_switch') : t('lang_switch');
                    toggleBtn.setAttribute('aria-label', window.SIGAP_I18N[lang].lang_switch);
                    toggleBtn.setAttribute('title', window.SIGAP_I18N[lang].lang_switch);
                }

                // Apply data-i18n (textContent)
                document.querySelectorAll('[data-i18n]').forEach(function (el) {
                    const key = el.getAttribute('data-i18n');
                    const val = window.SIGAP_I18N[lang] && window.SIGAP_I18N[lang][key];
                    if (val !== undefined) {
                        el.innerHTML = val;
                    }
                });

                // Apply data-i18n-title (title + aria-label for icon buttons)
                document.querySelectorAll('[data-i18n-title]').forEach(function (el) {
                    const key = el.getAttribute('data-i18n-title');
                    const val = window.SIGAP_I18N[lang] && window.SIGAP_I18N[lang][key];
                    if (val !== undefined) {
                        el.setAttribute('title', val);
                        el.setAttribute('aria-label', val);
                    }
                });

                // Apply data-i18n-aria (aria-label only)
                document.querySelectorAll('[data-i18n-aria]').forEach(function (el) {
                    const key = el.getAttribute('data-i18n-aria');
                    const val = window.SIGAP_I18N[lang] && window.SIGAP_I18N[lang][key];
                    if (val !== undefined) {
                        el.setAttribute('aria-label', val);
                    }
                });

                // Apply data-i18n-placeholder
                document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
                    const key = el.getAttribute('data-i18n-placeholder');
                    const val = window.SIGAP_I18N[lang] && window.SIGAP_I18N[lang][key];
                    if (val !== undefined) {
                        el.setAttribute('placeholder', val);
                    }
                });

                // Update clock locale
                if (window.SIGAP_CLOCK_LOCALE !== undefined) {
                    window.SIGAP_CLOCK_LOCALE = lang === 'en' ? 'en-US' : 'id-ID';
                }

                // Dispatch event so other scripts can hook into lang changes
                document.dispatchEvent(new CustomEvent('sigap:langchange', { detail: { lang: lang } }));
            }

            // Init on page load
            const initialLang = getLang();
            applyLang(initialLang);

            // Toggle button
            const langToggle = document.getElementById('lang-toggle');
            if (langToggle) {
                langToggle.addEventListener('click', function () {
                    const next = getLang() === 'en' ? 'id' : 'en';
                    localStorage.setItem('sigap-lang', next);
                    applyLang(next);
                });
            }

            window.SIGAP_APPLY_LANG = applyLang;
        })();

        // ============================================================
        // THEME TOGGLE
        // ============================================================
        const themeToggle = document.getElementById('theme-toggle');
        const updateThemeToggle = function () {
            const isDark = document.documentElement.dataset.theme === 'dark';
            const lang = localStorage.getItem('sigap-lang') === 'en' ? 'en' : 'id';
            const darkLabel  = window.SIGAP_I18N[lang].theme_dark;
            const lightLabel = window.SIGAP_I18N[lang].theme_light;
            if (!themeToggle) { return; }
            themeToggle.setAttribute('aria-label', isDark ? lightLabel : darkLabel);
            themeToggle.setAttribute('title', isDark ? lightLabel : darkLabel);
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
        // Re-apply theme toggle labels on lang change
        document.addEventListener('sigap:langchange', function () {
            updateThemeToggle();
        });

        // ============================================================
        // LIVE CLOCK
        // ============================================================
        window.SIGAP_CLOCK_LOCALE = localStorage.getItem('sigap-lang') === 'en' ? 'en-US' : 'id-ID';

        function updateClock() {
            const now = new Date();
            const locale = window.SIGAP_CLOCK_LOCALE || 'id-ID';
            const dateOptions = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const dateEl = document.getElementById('current-date');
            const timeEl = document.getElementById('current-time');
            if (dateEl) { dateEl.textContent = now.toLocaleDateString(locale, dateOptions); }
            if (timeEl) { timeEl.textContent = now.toLocaleTimeString(locale, timeOptions); }

            const fullOptions = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const liveClockEls = document.querySelectorAll('[data-live-clock]');
            liveClockEls.forEach(function (el) {
                el.textContent = now.toLocaleString(locale, fullOptions).replace(/\./g, ':').replace(',', ' ·');
            });
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Update clock locale on lang change
        document.addEventListener('sigap:langchange', function (e) {
            window.SIGAP_CLOCK_LOCALE = e.detail.lang === 'en' ? 'en-US' : 'id-ID';
        });

        // ============================================================
        // STATUS SELECT DYNAMIC STYLING
        // ============================================================
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

        // ============================================================
        // AUTH GATE — intercept protected nav links untuk guest
        // ============================================================
        (function () {
            const backdrop  = document.getElementById('auth-modal-backdrop');
            if (!backdrop) { return; }

            const closeBtn  = document.getElementById('auth-modal-close');
            const cancelBtn = document.getElementById('auth-modal-cancel-btn');
            const featureName = document.getElementById('auth-modal-feature-name');

            function openModal(featureLabel) {
                if (featureName) { featureName.textContent = featureLabel; }
                backdrop.classList.add('is-open');
                document.body.style.overflow = 'hidden';
                document.getElementById('auth-modal-close').focus();
            }

            function closeModal() {
                backdrop.classList.remove('is-open');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-requires-auth]').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    openModal(el.dataset.feature || 'Fitur Ini');
                });
            });

            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) { closeModal(); }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && backdrop.classList.contains('is-open')) { closeModal(); }
            });
        })();
    </script>

    @yield('scripts')
</body>

</html>