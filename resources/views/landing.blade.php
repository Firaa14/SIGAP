<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="DIKKP - Digitalisasi Informasi Kesiapan Keandalan Pembangkit. Pantau kondisi pembangkit, identifikasi gangguan, dan visualisasikan persebaran PLTA secara interaktif.">
    <meta name="robots" content="noindex, nofollow">
    <title>DIKKP — Sistem Informasi Kesiapan Keandalan Pembangkit</title>

    <link rel="icon" type="image/png" href="{{ asset('images/sigap-logo.png') }}">

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />

    {{-- Landing Page CSS --}}
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>

<body>

    <!-- ================================================================
     NAVBAR
     ================================================================ -->
    <nav class="lp-nav" id="lp-navbar">
        <div class="lp-nav-inner">
            {{-- Brand --}}
            <a href="{{ route('landing') }}" class="lp-nav-brand" id="lp-nav-brand">
                @php $logoPath = public_path('images/sigap-logo.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ asset('images/sigap-logo.png') }}" alt="SIGAP" class="lp-nav-logo">
                @endif
                <div class="lp-nav-brand-text">
                    <span class="lp-nav-brand-name">DIKKP</span>
                    <span class="lp-nav-brand-full">Digitalisasi Informasi Kesiapan Keandalan Pembangkit</span>
                </div>
            </a>

            {{-- Desktop menu --}}
            <ul class="lp-nav-menu" role="navigation" aria-label="Menu utama">
                <li><span class="lp-nav-link" data-scroll-to="lp-beranda" role="button" tabindex="0"
                        id="lp-nav-beranda">Beranda</span></li>
                <li><span class="lp-nav-link" data-scroll-to="lp-monitoring" role="button" tabindex="0"
                        id="lp-nav-monitoring">Monitoring</span></li>
                <li><span class="lp-nav-link" data-scroll-to="lp-features" role="button" tabindex="0"
                        id="lp-nav-features">Fitur</span></li>
                <li><span class="lp-nav-link" data-scroll-to="lp-guide" role="button" tabindex="0"
                        id="lp-nav-guide">Panduan</span></li>
            </ul>

            {{-- Desktop CTA --}}
            <div class="lp-nav-actions">
                <a href="{{ route('dashboard') }}" class="lp-nav-cta" id="lp-nav-dashboard-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Buka Dashboard
                </a>
            </div>

            {{-- Hamburger --}}
            <button class="lp-nav-hamburger" id="lp-hamburger" aria-label="Buka menu navigasi"
                aria-controls="lp-mobile-menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>

        {{-- Mobile menu --}}
        <div class="lp-mobile-menu" id="lp-mobile-menu" role="navigation" aria-label="Menu mobile">
            <ul class="lp-mobile-menu-list">
                <li><span data-scroll-to="lp-beranda" role="button" tabindex="0">Beranda</span></li>
                <li><span data-scroll-to="lp-monitoring" role="button" tabindex="0">Monitoring Gangguan</span></li>
                <li><span data-scroll-to="lp-features" role="button" tabindex="0">Fitur Utama</span></li>
                <li><span data-scroll-to="lp-guide" role="button" tabindex="0">Panduan Upload</span></li>
                <li>
                    <a href="{{ route('dashboard') }}" class="lp-mobile-menu-cta" id="lp-mobile-dashboard-btn">
                        Buka Dashboard DIKKP →
                    </a>
                </li>
            </ul>
        </div>
    </nav>


    <!-- ================================================================
     HERO SECTION
     ================================================================ -->
    <section class="lp-hero" id="lp-beranda" aria-label="Beranda DIKKP">
        {{-- Background --}}
        <div class="lp-hero-bg" aria-hidden="true">
            <div class="lp-hero-bg-grid"></div>
            <div class="lp-hero-bg-glow lp-hero-bg-glow-1"></div>
            <div class="lp-hero-bg-glow lp-hero-bg-glow-2"></div>
        </div>

        <div class="lp-hero-inner">
            {{-- Text content --}}
            <div class="lp-hero-content">

                <h1 class="lp-hero-headline">
                    Digitalisasi Informasi Kesiapan Keandalan Pembangkit<br>
                    <span class="lp-hl-accent">Laporan Non-Rutin</span>
                </h1>

                <p class="lp-hero-desc">
                    Digitalisasi Informasi Kesiapan Keandalan Pembangkit sebagai sarana monitoring terpadu kesehatan unit dan
                    equipment PLTA untuk mendukung keandalan operasional pembangkitan.
                </p>

                <div class="lp-hero-actions">
                    <a href="{{ route('dashboard') }}" class="lp-btn lp-btn-primary lp-btn-lg"
                        id="lp-hero-dashboard-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Buka Dashboard DIKKP
                    </a>
                    <button type="button" class="lp-btn lp-btn-secondary lp-btn-lg" data-scroll-to="lp-features"
                        id="lp-hero-explore-btn">
                        Jelajahi Fitur
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>

                {{-- Metrics from DB --}}
                @if($hasData)
                    <div class="lp-hero-metrics" aria-label="Ringkasan statistik sistem">
                        <div class="lp-hero-metric">
                            <span class="lp-hero-metric-val">{{ $stats['total_plta'] }}</span>
                            <span class="lp-hero-metric-lbl">PLTA Dipantau</span>
                        </div>
                        <div class="lp-hero-metric">
                            <span class="lp-hero-metric-val">{{ $stats['total_equipment'] }}</span>
                            <span class="lp-hero-metric-lbl">Total Equipment</span>
                        </div>
                        <div class="lp-hero-metric">
                            <span class="lp-hero-metric-val" style="color: #34D399;">{{ $stats['normal'] }}</span>
                            <span class="lp-hero-metric-lbl">Status Normal</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Visual (Slider Foto PLTA) --}}
            @php
                $heroSlides = [
                    ['file' => 'PLTA AMPELGADING.jpeg', 'alt' => 'PLTA Ampelgading', 'fit' => 'contain'],
                    ['file' => 'PLTA GIRINGAN.png',    'alt' => 'PLTA Giringan'],
                    ['file' => 'PLTA GOLANG.jpg',      'alt' => 'PLTA Golang'],
                    ['file' => 'PLTA LODOYO.jpg',      'alt' => 'PLTA Lodoyo'],
                    ['file' => 'PLTA MENDALAN.jpg',    'alt' => 'PLTA Mendalan'],
                    ['file' => 'PLTA NGEBEL.jpg',      'alt' => 'PLTA Ngebel'],
                    ['file' => 'PLTA SELOREJO.jpg',    'alt' => 'PLTA Selorejo'],
                    ['file' => 'PLTA SENGGURUH.png',   'alt' => 'PLTA Sengguruh'],
                    ['file' => 'PLTA SIMAN.jpg',       'alt' => 'PLTA Siman'],
                    ['file' => 'PLTA SUTAMI.jpg',      'alt' => 'PLTA Sutami'],
                    ['file' => 'PLTA TULUNGAGUNG.png', 'alt' => 'PLTA Tulungagung'],
                    ['file' => 'PLTA WLINGI.jpg',      'alt' => 'PLTA Wlingi'],
                    ['file' => 'PLTA WONOREJO.jpg',    'alt' => 'PLTA Wonorejo'],
                ];
            @endphp

            <div class="lp-hero-visual">
                <div class="lp-hero-img-wrap lp-slider" id="lpSlider">
                    <div class="lp-slides">
                        @foreach($heroSlides as $i => $s)
                            <div class="lp-slide {{ $i === 0 ? 'is-active' : '' }} {{ !empty($s['fit']) ? 'lp-slide--' . $s['fit'] : '' }}">
                                <img src="{{ asset('images/' . rawurlencode($s['file'])) }}"
                                     alt="{{ $s['alt'] }}"
                                     class="lp-hero-img"
                                     loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
                            </div>
                        @endforeach
                    </div>

                    <div class="lp-hero-img-overlay"></div>

                    <button type="button" class="lp-slider-nav lp-slider-prev" aria-label="Foto sebelumnya">&#10094;</button>
                    <button type="button" class="lp-slider-nav lp-slider-next" aria-label="Foto berikutnya">&#10095;</button>
                    <div class="lp-slider-dots"></div>
                </div>
            </div>
        </div>
    </section>


    <!-- ================================================================
     MONITORING STATUS SECTION
     ================================================================ -->
    <section class="lp-monitoring lp-section" id="lp-monitoring" aria-label="Ringkasan status gangguan">
        <div class="lp-container">

            <div class="lp-monitoring-header">
                <div>
                    <h2 class="lp-section-title lp-reveal lp-reveal-delay-1">Status Operasional Terkini</h2>
                    <p class="lp-section-desc lp-reveal lp-reveal-delay-2">
                        Ringkasan kondisi equipment pembangkit berdasarkan data aktual dari sistem DIKKP.
                        Data diperbarui secara berkala sesuai upload terakhir yang dilakukan oleh System Owner.
                    </p>
                </div>

                @if($hasData)
                    <div class="lp-monitoring-meta lp-reveal lp-reveal-delay-2">
                        <span class="lp-monitoring-live-dot" aria-hidden="true"></span>
                        <span>
                            @if($lastUpdatedAt)
                                Diperbarui: <span id="lp-last-upload-time">{{ $lastUpdatedAt }}</span>
                            @else
                                Menunggu data upload pertama
                            @endif
                        </span>
                    </div>
                @endif
            </div>

            @if($hasData)
                <div class="lp-stat-grid" role="list" aria-label="Statistik status equipment">

                    <div class="lp-stat-card lp-stat-card--blue lp-reveal" role="listitem">
                        <div class="lp-stat-icon" aria-hidden="true">⚡</div>
                        <div class="lp-stat-body">
                            <div class="lp-stat-value" id="lp-stat-total-plta">{{ $stats['total_plta'] }}</div>
                            <div class="lp-stat-label">Total PLTA</div>
                        </div>
                    </div>

                    <div class="lp-stat-card lp-stat-card--purple lp-reveal lp-reveal-delay-1" role="listitem">
                        <div class="lp-stat-icon" aria-hidden="true">⚙</div>
                        <div class="lp-stat-body">
                            <div class="lp-stat-value" id="lp-stat-total-equip">{{ $stats['total_equipment'] }}</div>
                            <div class="lp-stat-label">Total Equipment</div>
                        </div>
                    </div>

                    <div class="lp-stat-card lp-stat-card--green lp-reveal lp-reveal-delay-2" role="listitem">
                        <div class="lp-stat-icon" aria-hidden="true">✓</div>
                        <div class="lp-stat-body">
                            <div class="lp-stat-value" id="lp-stat-normal">{{ $stats['normal'] }}</div>
                            <div class="lp-stat-label">Status Normal</div>
                        </div>
                    </div>

                    <div class="lp-stat-card lp-stat-card--amber lp-reveal lp-reveal-delay-3" role="listitem">
                        <div class="lp-stat-icon" aria-hidden="true">⚠</div>
                        <div class="lp-stat-body">
                            <div class="lp-stat-value" id="lp-stat-abnormal">{{ $stats['abnormal'] }}</div>
                            <div class="lp-stat-label">Status Abnormal</div>
                        </div>
                    </div>

                    <div class="lp-stat-card lp-stat-card--red lp-reveal lp-reveal-delay-4" role="listitem">
                        <div class="lp-stat-icon" aria-hidden="true">⏸</div>
                        <div class="lp-stat-body">
                            <div class="lp-stat-value" id="lp-stat-not-ready">{{ $stats['not_ready'] }}</div>
                            <div class="lp-stat-label">Not Ready</div>
                        </div>
                    </div>

                </div>

                <div class="lp-update-info lp-reveal lp-reveal-delay-5" aria-live="polite">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Waktu pembaruan merupakan data upload terakhir oleh System Owner.
                </div>

            @else
                <div class="lp-no-data-notice lp-reveal" role="status" aria-live="polite">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>
                        <strong>Data belum tersedia.</strong>
                        Statistik operasional akan ditampilkan setelah System Owner melakukan upload data pertama melalui
                        dashboard DIKKP.
                    </span>
                </div>
            @endif

        </div>
    </section>


    <!-- ================================================================
     FITUR UTAMA
     ================================================================ -->
    <section class="lp-features" id="lp-features" aria-label="Fitur utama DIKKP">
        <div class="lp-container">

            <div class="lp-features-header">
                <h2 class="lp-section-title lp-reveal lp-reveal-delay-1">Kemampuan Sistem DIKKP</h2>
                <p class="lp-section-desc lp-reveal lp-reveal-delay-2" style="margin:0 auto;">
                    Rangkaian fitur terintegrasi yang dirancang untuk mendukung pemantauan dan analisis gangguan
                    pembangkit secara efektif.
                </p>
            </div>

            <div class="lp-features-grid" role="list">

                {{-- 1. Upload --}}
                <div class="lp-feature-card lp-reveal" role="listitem">
                    <div class="lp-feature-icon-wrap lp-fi-blue" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <h3 class="lp-feature-title">Upload & Pemrosesan Data</h3>
                    <p class="lp-feature-desc">
                        Pengguna dengan otoritas System Owner (SO) dapat mengunggah file Excel WO yang sudah difilter.
                        Sistem memproses data secara otomatis untuk mengidentifikasi gangguan dan aset terdampak.
                    </p>
                </div>

                {{-- 2. Monitoring --}}
                <div class="lp-feature-card lp-reveal lp-reveal-delay-1" role="listitem">
                    <div class="lp-feature-icon-wrap lp-fi-green" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </div>
                    <h3 class="lp-feature-title">Monitoring Status Operasional</h3>
                    <p class="lp-feature-desc">
                        Menampilkan ringkasan kondisi aset atau unit pembangkit berdasarkan tiga kategori status:
                        <strong>Normal</strong>, <strong>Abnormal</strong>, dan <strong>Not Ready</strong>.
                        Data bersumber langsung dari database sistem dan dapat diakses tanpa harus login.
                    </p>
                </div>


                {{-- 4. Peta --}}
                <div class="lp-feature-card lp-reveal lp-reveal-delay-3" role="listitem">
                    <div class="lp-feature-icon-wrap lp-fi-purple" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                            <line x1="8" y1="2" x2="8" y2="18"></line>
                            <line x1="16" y1="6" x2="16" y2="22"></line>
                        </svg>
                    </div>
                    <h3 class="lp-feature-title">Pemetaan Geografis</h3>
                    <p class="lp-feature-desc">
                        Peta interaktif yang membantu pengguna memahami persebaran unit pembangkit
                        berdasarkan lokasi geografisnya, dilengkapi dengan penanda status berwarna,
                        tooltip informasi, dan kemampuan filter berdasarkan PLTA atau status gangguan.
                    </p>
                </div>


            </div>
        </div>
    </section>


    <!-- ================================================================
     PANDUAN UPLOAD DATA (SO)
     ================================================================ -->
    <section class="lp-guide lp-section-alt" id="lp-guide" aria-label="Panduan upload data oleh System Owner">
        <div class="lp-container">

            <div class="lp-guide-header">
                <h2 class="lp-section-title lp-reveal lp-reveal-delay-1">Alur Upload Data oleh System Owner</h2>
                <p class="lp-section-desc lp-reveal lp-reveal-delay-2">
                    Fitur upload data Work Order hanya dapat dilakukan melalui dashboard DIKKP oleh pengguna dengan
                    otoritas System Owner (SO).
                    Panduan berikut menjelaskan alur proses upload berdasarkan alur yang tersedia di sistem.
                </p>
            </div>

            {{-- Notice --}}
            <div class="lp-guide-notice lp-reveal lp-reveal-delay-2">
                <div class="lp-guide-notice-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div>
                    <strong>Proses upload hanya dapat dilakukan melalui Dashboard DIKKP.</strong>
                    Fitur ini memerlukan autentikasi sebagai <strong>System Owner (SO)</strong> atau
                    <strong>CBM</strong>.
                    Landing page ini bersifat informatif dan tidak menyediakan akses upload langsung.
                </div>
            </div>

            {{-- Step by step guide --}}
            <div class="lp-guide-steps" role="list" aria-label="Langkah-langkah upload data">

                <div class="lp-guide-step lp-reveal" role="listitem">
                    <div class="lp-guide-step-num" aria-label="Langkah 1">1</div>
                    <div class="lp-guide-step-content">
                        <h3 class="lp-guide-step-title">Login ke Dashboard DIKKP</h3>
                        <p class="lp-guide-step-desc">
                            Buka dashboard DIKKP melalui tombol "Buka Dashboard DIKKP" di halaman ini.
                            Sistem akan menampilkan halaman dashboard. Untuk mengakses fitur upload,
                            klik tombol Login yang tersedia di header dashboard dan masukkan kredensial
                            akun dengan otoritas System Owner (SO) atau CBM.
                        </p>
                        <span class="lp-guide-step-tag auth">🔐 Autentikasi diperlukan</span>
                    </div>
                </div>

                <div class="lp-guide-step lp-reveal lp-reveal-delay-1" role="listitem">
                    <div class="lp-guide-step-num" aria-label="Langkah 2">2</div>
                    <div class="lp-guide-step-content">
                        <h3 class="lp-guide-step-title">Siapkan File Excel Work Order</h3>
                        <p class="lp-guide-step-desc">
                            Siapkan file Excel (<code>.xlsx</code> atau <code>.xls</code>) hasil Power Query yang sudah
                            difilter secara manual.
                            File harus memiliki kolom wajib: <strong>NO WO, DESCRIPTION, WORKTYPE, STATUS,
                                ASSETNUM</strong>.
                            Pastikan file hanya mengandung data WO dengan Worktype yang diizinkan:
                            <strong>CM, EJ, EV, CP, PAM</strong>. Hapus baris dengan Worktype lain sebelum upload.
                        </p>
                        <span class="lp-guide-step-tag info">📄 Format: .xlsx / .xls, maks 20 MB</span>
                    </div>
                </div>

                <div class="lp-guide-step lp-reveal lp-reveal-delay-2" role="listitem">
                    <div class="lp-guide-step-num" aria-label="Langkah 3">3</div>
                    <div class="lp-guide-step-content">
                        <h3 class="lp-guide-step-title">Buka Menu Upload Data WO</h3>
                        <p class="lp-guide-step-desc">
                            Di sidebar dashboard DIKKP, klik menu <strong>"Upload Data WO"</strong>.
                            Halaman upload akan menampilkan area drop zone untuk memilih atau menyeret file.
                            Klik <strong>"Pilih File"</strong> atau seret file Excel ke area yang tersedia.
                            Setelah file dipilih, klik <strong>"Tampilkan Preview"</strong> untuk melanjutkan proses
                            validasi.
                        </p>
                        <span class="lp-guide-step-tag so">👤 Hanya SO & CBM</span>
                    </div>
                </div>

                <div class="lp-guide-step lp-reveal lp-reveal-delay-3" role="listitem">
                    <div class="lp-guide-step-num" aria-label="Langkah 4">4</div>
                    <div class="lp-guide-step-content">
                        <h3 class="lp-guide-step-title">Periksa Hasil Validasi & Preview</h3>
                        <p class="lp-guide-step-desc">
                            Sistem akan menampilkan ringkasan hasil validasi file, mencakup total baris yang dibaca,
                            jumlah data valid, data tidak valid, serta distribusi PLTA berdasarkan ASSETNUM.
                            Periksa tabel preview untuk memastikan data yang akan diimport sudah sesuai.
                            Jika terdapat baris tidak valid, sistem menampilkan keterangan kesalahannya.
                        </p>
                        <span class="lp-guide-step-tag info">✓ Cek sebelum konfirmasi</span>
                    </div>
                </div>

                <div class="lp-guide-step lp-reveal lp-reveal-delay-4" role="listitem">
                    <div class="lp-guide-step-num" aria-label="Langkah 5">5</div>
                    <div class="lp-guide-step-content">
                        <h3 class="lp-guide-step-title">Konfirmasi & Import Data</h3>
                        <p class="lp-guide-step-desc">
                            Jika hasil validasi sudah sesuai, klik <strong>"Konfirmasi &amp; Import Data"</strong>.
                            Sistem akan memproses dan mendistribusikan data WO ke seluruh PLTA terkait berdasarkan
                            kode prefix ASSETNUM. Tunggu hingga proses selesai. <strong>Jangan tutup atau refresh
                                halaman</strong>
                            selama proses berlangsung. Status operasional equipment akan diperbarui otomatis setelah
                            import berhasil.
                        </p>
                        <span class="lp-guide-step-tag so">⚡ Status diperbarui otomatis</span>
                    </div>
                </div>

                <div class="lp-guide-step lp-reveal lp-reveal-delay-5" role="listitem">
                    <div class="lp-guide-step-num" aria-label="Langkah 6">6</div>
                    <div class="lp-guide-step-content">
                        <h3 class="lp-guide-step-title">Lihat Hasil Import di Dashboard</h3>
                        <p class="lp-guide-step-desc">
                            Setelah import berhasil, sistem menampilkan halaman ringkasan hasil import.
                            Dashboard akan menampilkan statistik terbaru beserta peta distribusi PLTA
                            yang sudah diperbarui. Data dapat dilihat oleh seluruh pengguna yang mengakses
                            sistem, termasuk Reviewer tanpa perlu login.
                        </p>
                        <span class="lp-guide-step-tag info">📊 Tersedia untuk semua pengguna</span>
                    </div>
                </div>

            </div>

            {{-- Rule summary --}}
            <div style="margin-top:48px;background:var(--color-white);border:1px solid var(--color-gray-200);border-radius:16px;padding:28px;display:grid;grid-template-columns:1fr 1fr;gap:24px;"
                class="lp-reveal">
                <div>
                    <div
                        style="font-size:12px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;color:var(--color-gray-400);margin-bottom:12px;">
                        Status WO → Status Operasi</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
                            <span
                                style="padding:3px 10px;border-radius:6px;background:#FEF3C7;color:#92400E;font-weight:700;font-size:11px;white-space:nowrap;">APPR
                                / INPRG / PTWCL / PTWR / WPTW</span>
                            <span style="color:var(--color-gray-500);">→</span>
                            <span
                                style="padding:3px 10px;border-radius:6px;background:var(--color-abnormal-bg);color:#B45309;font-weight:700;font-size:11px;">ABNORMAL</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
                            <span
                                style="padding:3px 10px;border-radius:6px;background:var(--color-blue-light);color:#1D4ED8;font-weight:700;font-size:11px;white-space:nowrap;">CLOSE
                                / COMP</span>
                            <span style="color:var(--color-gray-500);">→</span>
                            <span
                                style="padding:3px 10px;border-radius:6px;background:var(--color-normal-bg);color:#065F46;font-weight:700;font-size:11px;">NORMAL</span>
                        </div>
                    </div>
                </div>
                <div>
                    <div
                        style="font-size:12px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;color:var(--color-gray-400);margin-bottom:12px;">
                        Worktype yang Diizinkan</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach(['CM', 'EJ', 'EV', 'CP', 'PAM'] as $wt)
                            <span
                                style="padding:4px 12px;border-radius:7px;background:var(--color-blue-light);color:var(--color-accent);font-weight:700;font-size:12px;">{{ $wt }}</span>
                        @endforeach
                    </div>
                    <div style="margin-top:10px;font-size:12px;color:var(--color-gray-400);">
                        4 karakter pertama ASSETNUM menentukan PLTA tujuan distribusi data.
                    </div>
                </div>
            </div>

        </div>
    </section>


    <!-- ================================================================
     ALUR AKSES & KEAMANAN
     ================================================================ -->
    <section class="lp-flow" id="lp-flow" aria-label="Alur akses dan keamanan sistem DIKKP">
        <div class="lp-container">

            <div class="lp-flow-header">
                <h2 class="lp-section-title lp-reveal lp-reveal-delay-1">Cara Menggunakan DIKKP</h2>
                <p class="lp-section-desc lp-reveal lp-reveal-delay-2">
                    Alur penggunaan sistem dari landing page hingga fitur operasional yang tersedia sesuai peran
                    pengguna.
                </p>
            </div>

            {{-- Flow steps --}}
            <div class="lp-flow-steps lp-reveal lp-reveal-delay-2" role="list" aria-label="Tahapan alur penggunaan">

                <div class="lp-flow-step" role="listitem">
                    <div class="lp-flow-step-num"><span class="lp-flow-step-icon" aria-hidden="true">🌐</span></div>
                    <div class="lp-flow-step-title">Buka Landing Page</div>
                    <div class="lp-flow-step-desc">Pengguna membuka alamat utama DIKKP dan melihat ringkasan sistem
                    </div>
                </div>

                <div class="lp-flow-step" role="listitem">
                    <div class="lp-flow-step-num"><span class="lp-flow-step-icon" aria-hidden="true">➡</span></div>
                    <div class="lp-flow-step-title">Buka Dashboard</div>
                    <div class="lp-flow-step-desc">Klik tombol "Buka Dashboard DIKKP" untuk mengakses sistem pemantauan
                    </div>
                </div>

                <div class="lp-flow-step" role="listitem">
                    <div class="lp-flow-step-num"><span class="lp-flow-step-icon" aria-hidden="true">📊</span></div>
                    <div class="lp-flow-step-title">Akses Dashboard</div>
                    <div class="lp-flow-step-desc">Dashboard DIKKP terbuka, menampilkan data terkini sesuai hak akses
                    </div>
                </div>

                <div class="lp-flow-step" role="listitem">
                    <div class="lp-flow-step-num"><span class="lp-flow-step-icon" aria-hidden="true">🔐</span></div>
                    <div class="lp-flow-step-title">Autentikasi (Opsional)</div>
                    <div class="lp-flow-step-desc">Fitur write/edit meminta login. Klik menu → sistem meminta
                        autentikasi</div>
                </div>

                <div class="lp-flow-step" role="listitem">
                    <div class="lp-flow-step-num"><span class="lp-flow-step-icon" aria-hidden="true">✅</span></div>
                    <div class="lp-flow-step-title">Akses Sesuai Peran</div>
                    <div class="lp-flow-step-desc">Setelah login berhasil, pengguna menjalankan tindakan sesuai perannya
                    </div>
                </div>

            </div>

            {{-- Role cards --}}
            <div class="lp-flow-roles lp-reveal lp-reveal-delay-3" role="list" aria-label="Hak akses berdasarkan peran">

                <div class="lp-flow-role-card" role="listitem">
                    <div class="lp-flow-role-name">
                        Reviewer
                        <span class="lp-flow-role-badge review">READ</span>
                    </div>
                    <p class="lp-flow-role-desc">Pengguna yang mengakses sistem tanpa login atau dengan akun Reviewer.
                    </p>
                    <div class="lp-flow-role-perms">
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">✓</span> Lihat
                            dashboard & statistik</span>
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">✓</span> Lihat data
                            PLTA & equipment</span>
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">✓</span> Lihat peta
                            persebaran</span>
                        <span class="lp-flow-role-perm"><span class="cross" aria-hidden="true">✕</span> Upload / ubah
                            data</span>
                    </div>
                </div>

                <div class="lp-flow-role-card" role="listitem">
                    <div class="lp-flow-role-name">
                        System Owner
                        <span class="lp-flow-role-badge so">SO</span>
                    </div>
                    <p class="lp-flow-role-desc">Pengguna dengan otoritas penuh untuk mengelola data sistem.</p>
                    <div class="lp-flow-role-perms">
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">✓</span> Semua hak
                            akses Reviewer</span>
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">✓</span> Upload data
                            Work Order</span>
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">✓</span> Insert
                            equipment baru</span>
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">✓</span> Update
                            status equipment</span>
                    </div>
                </div>

                <div class="lp-flow-role-card" role="listitem">
                    <div class="lp-flow-role-name">
                        CBM
                        <span class="lp-flow-role-badge cbm">CBM</span>
                    </div>
                    <p class="lp-flow-role-desc">Pengguna dengan hak akses pengelolaan yang dikonfigurasi di sistem.</p>
                    <div class="lp-flow-role-perms">
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">-</span>Soon</span>
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">-</span>Soon</span>
                        <span class="lp-flow-role-perm"><span class="checkmark" aria-hidden="true">-</span>Soon</span>
                    </div>
                </div>

            </div>

        </div>
    </section>


    <!-- ================================================================
     CALL TO ACTION
     ================================================================ -->
    <section class="lp-cta" id="lp-cta" aria-label="Ajakan mengakses dashboard DIKKP">
        <div class="lp-cta-glow" aria-hidden="true"></div>
        <div class="lp-container">
            <div class="lp-cta-inner">
                <h2 class="lp-cta-title lp-reveal">
                    Akses Informasi Gangguan<br>Pembangkit dalam Satu Sistem.
                </h2>
                <p class="lp-cta-desc lp-reveal lp-reveal-delay-1">
                    Gunakan SIGAP untuk membantu pemantauan kondisi pembangkit, mengidentifikasi gangguan,
                    dan memperoleh informasi operasional secara lebih terstruktur.
                </p>
                <div class="lp-cta-actions lp-reveal lp-reveal-delay-2">
                    <a href="{{ route('dashboard') }}" class="lp-btn lp-btn-primary lp-btn-lg"
                        id="lp-cta-dashboard-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Buka Dashboard DIKKP
                    </a>
                    <button type="button" class="lp-btn lp-btn-secondary lp-btn-lg" data-scroll-to="lp-guide"
                        id="lp-cta-guide-btn">
                        Lihat Panduan Upload
                    </button>
                </div>
            </div>
        </div>
    </section>

    <footer class="lp-footer lp-footer-compact">
        <div class="lp-container">
            <div class="lp-footer-copyright">© {{ date('Y') }} DIKKP by Intern Universitas Brawijaya.</div>
        </div>
    </footer>


    {{-- ================================================================
    DATA & SCRIPTS
    ================================================================ --}}
    <script>
        // Pass server-side data ke JavaScript
        window.SIGAP_LANDING_MAP_DATA = @json($mapData);
        window.SIGAP_LANDING_STATS_URL = @json(route('landing.stats'));
    </script>

    {{-- Leaflet JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    {{-- Landing Page JS --}}
    <script src="{{ asset('js/landing.js') }}"></script>

</body>

</html>