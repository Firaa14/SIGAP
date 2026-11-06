@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item">DIKKP</li>
    <li class="breadcrumb-item">Dashboard</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <style>
        a.stat-card {
            display: flex;
            text-decoration: none;
            color: inherit;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        a.stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, .25);
            text-decoration: none;
            color: inherit;
        }

        /* ===== Khusus peran CBM ===== */
        .cbm-param-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .cbm-param-grid .stat-card.is-active {
            outline: 2px solid var(--color-accent, #4A9EFF);
        }

        @media (max-width: 1400px) {
            .cbm-param-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 700px) {
            .cbm-param-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        .cbm-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 14px 20px 0;
        }

        .cbm-tab {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 999px;
            border: 1px solid var(--border-color, #ddd);
            color: var(--text-muted);
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }

        .cbm-tab:hover {
            text-decoration: none;
            color: var(--text-primary);
        }

        .cbm-tab.active {
            background: var(--color-accent, #4A9EFF);
            border-color: var(--color-accent, #4A9EFF);
            color: #fff;
        }
    </style>
@endpush

@section('content')

    <div class="stats-grid" id="stats-overview"
        @if (! empty($cbmParameterStats)) style="grid-template-columns:repeat(2,minmax(0,1fr));" @endif>

        <div class="stat-card">
            <div class="stat-icon blue">⚡</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="total_plta">{{ $stats['total_plta'] }}</div>
                <div class="stat-label" data-i18n="dash_total_plta">Total PLTA</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">⚙</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="total_equipment">{{ $stats['total_equipment'] }}</div>
                <div class="stat-label" data-i18n="dash_total_equipment">Total Equipment</div>
            </div>
        </div>

        @if (empty($cbmParameterStats))

            <a href="{{ route('status.index', ['status' => 'normal']) }}" class="stat-card">
                <div class="stat-icon green">✓</div>
                <div class="stat-body">
                    <div class="stat-value" data-stat="normal">{{ $stats['normal'] }}</div>
                    <div class="stat-label" data-i18n="dash_status_normal">Status Normal</div>
                </div>
            </a>

            <a href="{{ route('status.index', ['status' => 'abnormal']) }}" class="stat-card">
                <div class="stat-icon amber">!</div>
                <div class="stat-body">
                    <div class="stat-value" data-stat="abnormal">{{ $stats['abnormal'] }}</div>
                    <div class="stat-label" data-i18n="dash_status_abnormal">Status Abnormal</div>
                </div>
            </a>

            <div class="stat-card">
                <div class="stat-icon red">⏸</div>
                <div class="stat-body">
                    <div class="stat-value" data-stat="not_ready">{{ $stats['not_ready'] }}</div>
                    <div class="stat-label" data-i18n="dash_status_not_ready">Not Ready</div>
                </div>
            </div>

        @endif

    </div>

    @if (! empty($cbmParameterStats))
        {{-- Peran CBM: satu baris kartu parameter, bisa diklik --}}
        @php
            $cbmIconPalette = ['green', 'blue', 'amber', 'purple', 'red', 'blue'];
        @endphp
        <div class="cbm-param-grid" id="cbm-param-cards">
            @foreach ($cbmParameterStats as $i => $param)
                <a href="{{ route('dashboard', ['parameter' => $param['key']]) }}#recent-activity-card"
                    class="stat-card {{ ($selectedParameter ?? null) === $param['key'] ? 'is-active' : '' }}">
                    <div class="stat-icon {{ $cbmIconPalette[$i % count($cbmIconPalette)] }}">◉</div>
                    <div class="stat-body">
                        <div class="stat-value" data-cbm-param="{{ $param['key'] }}">{{ $param['total'] }}</div>
                        <div class="stat-label">{{ $param['label'] }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <div class="card map-card" id="plta-map-card">
        <div class="card-header">
            <div class="card-title">
                <span class="card-title-icon">📍</span>
                <span data-i18n="dash_map_title">Peta Sebaran PLTA UP Brantas</span>
            </div>
            <div class="map-toolbar">
                <div class="map-legend">
                    <span class="map-legend-item"><span class="map-legend-dot normal"></span> <span
                            data-i18n="status_normal">Normal</span></span>
                    <span class="map-legend-item"><span class="map-legend-dot not_ready"></span> <span
                            data-i18n="status_not_ready">Not Ready</span></span>
                    <span class="map-legend-item"><span class="map-legend-dot abnormal"></span> <span
                            data-i18n="status_abnormal">Abnormal</span></span>
                </div>
                <label class="map-filter-label" for="map-status-filter">
                    <select id="map-status-filter" class="map-status-filter" aria-label="Filter status PLTA">
                        <option value="all" data-i18n="dash_filter_status">Filter Status</option>
                        <option value="normal" data-i18n="status_normal">Normal</option>
                        <option value="abnormal" data-i18n="status_abnormal">Abnormal</option>
                    </select>
                </label>
                <button type="button" class="map-toggle-btn" id="map-toggle-labels" data-i18n="dash_show_labels">Tampilkan
                    Semua Label</button>
            </div>
        </div>
        <div class="map-wrapper">
            <div id="plta-map" role="img" aria-label="Distribution map of 13 PLTA UP Brantas locations"
                data-i18n-aria="dash_map_aria"></div>
        </div>
    </div>

    @if (isset($cbmActivity) && $cbmActivity !== null)
        {{-- Peran CBM: aktivitas upload data CBM --}}
        <div class="card" id="recent-activity-card" style="margin-top:16px;">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-title-icon">◷</span>
                    <span>
                        @if (! empty($selectedParameter))
                            Data {{ $cbmParameterStats[array_search($selectedParameter, array_column($cbmParameterStats, 'key'))]['label'] ?? $selectedParameter }}
                            ({{ count($cbmActivity) }})
                        @else
                            Aktivitas Status Terbaru
                        @endif
                    </span>
                </div>
                <div class="card-actions">
                    <span style="font-size:11px; color:var(--text-muted)">Update: {{ date('d M Y') }}</span>
                </div>
            </div>

            @php
                // Tab hanya untuk parameter yang sudah pernah diupload (jumlah data > 0).
                $uploadedParams = collect($cbmParameterStats)->filter(fn ($p) => $p['total'] > 0);
            @endphp
            @if ($uploadedParams->isNotEmpty())
                <div class="cbm-tabs">
                    <a href="{{ route('dashboard') }}#recent-activity-card"
                        class="cbm-tab {{ empty($selectedParameter) ? 'active' : '' }}">Semua</a>
                    @foreach ($uploadedParams as $param)
                        <a href="{{ route('dashboard', ['parameter' => $param['key']]) }}#recent-activity-card"
                            class="cbm-tab {{ ($selectedParameter ?? null) === $param['key'] ? 'active' : '' }}">{{ $param['label'] }}</a>
                    @endforeach
                </div>
            @endif

            <div class="table-wrapper">
                @if (count($cbmActivity) > 0)
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Waktu Upload</th>
                                <th>PLTA</th>
                                <th>ASSETNUM</th>
                                <th>Parameter</th>
                                <th>Status</th>
                                <th>Tanggal Uji</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cbmActivity as $row)
                                <tr>
                                    <td><span class="activity-time">{{ $row['time'] }}</span></td>
                                    <td style="font-size:12.5px; font-weight:500;">{{ $row['plta'] }}</td>
                                    <td><span class="activity-assetnum">{{ $row['assetnum'] }}</span></td>
                                    <td style="font-size:12.5px;">{{ $row['parameter'] }}</td>
                                    <td>
                                        <span class="status-badge"
                                            style="background:{{ $row['color'] }}; color:#fff;"
                                            title="{{ $row['label'] }}">{{ $row['code'] }}</span>
                                        <span style="font-size:12px; margin-left:6px;">{{ $row['label'] }}</span>
                                    </td>
                                    <td style="font-size:12.5px;">{{ $row['measured_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state" style="padding:32px 0;">
                        <div class="empty-state-icon">◷</div>
                        <div class="empty-state-title">Belum ada data</div>
                        <div class="empty-state-text">
                            @if (! empty($selectedParameter))
                                Belum ada data uji untuk parameter ini.
                            @else
                                Upload file Excel CBM untuk melihat aktivitas terbaru.
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
    <div class="card" id="recent-activity-card" style="margin-top:16px;">
        <div class="card-header">
            <div class="card-title">
                <span class="card-title-icon">◷</span>
                <span data-i18n="dash_activity_title">Aktivitas Status Terbaru</span>
            </div>
            <div class="card-actions">
                <span style="font-size:11px; color:var(--text-muted)">
                    <span data-i18n="dash_update">Update:</span> {{ date('d M Y') }}
                </span>
            </div>
        </div>
        <div class="table-wrapper">
            @if(count($recentActivity) > 0)
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th data-i18n="dash_th_upload_time">Upload Time</th>
                            <th data-i18n="dash_th_plta">PLTA</th>
                            <th data-i18n="dash_th_assetnum">ASSETNUM</th>
                            <th data-i18n="dash_th_status">Status</th>
                            <th data-i18n="dash_th_wo">No WO</th>
                            <th data-i18n="dash_th_report_date">Tanggal Report</th>
                            <th data-i18n="dash_th_duration">Durasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivity as $activity)
                            @php
                                $statusClass = match ($activity['status']) {
                                    'Normal' => 'normal',
                                    'Abnormal' => 'abnormal',
                                    default => 'not-ready',
                                };
                            @endphp
                            <tr>
                                <td><span class="activity-time">{{ $activity['time'] }}</span></td>
                                <td style="font-size:12.5px; font-weight:500;">{{ $activity['plta'] }}</td>
                                <td><span class="activity-assetnum">{{ $activity['assetnum'] }}</span></td>
                                <td>
                                    <span class="status-badge {{ $statusClass }}">{{ $activity['status'] }}</span>
                                </td>
                                <td class="activity-wo">
                                    {{ $activity['wo'] }}
                                </td>
                                <td style="font-size:12.5px;">{{ $activity['report_date'] }}</td>
                                <td style="font-size:12.5px;">{{ $activity['durasi_hari'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state" style="padding:32px 0;">
                    <div class="empty-state-icon">◷</div>
                    <div class="empty-state-title" data-i18n="dash_empty_title">Belum ada aktivitas</div>
                    <div class="empty-state-text" data-i18n="dash_empty_text">Upload file Excel WO untuk melihat aktivitas
                        terbaru.</div>
                </div>
            @endif
        </div>
    </div>
    @endif

@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        window.SIGAP_MAP_DATA = @json($mapData);
        window.SIGAP_MAP_DATA_URL = @json(route('dashboard.map-data'));
    </script>
    <script src="{{ asset('js/dashboard-map.js') }}"></script>
    <script>
        // Sync map toggle button label on lang change
        (function () {
            const mapToggleBtn = document.getElementById('map-toggle-labels');
            if (!mapToggleBtn) { return; }

            // Track toggle state
            let labelsVisible = false;
            mapToggleBtn.addEventListener('click', function () {
                labelsVisible = !labelsVisible;
                const lang = localStorage.getItem('sigap-lang') === 'en' ? 'en' : 'id';
                mapToggleBtn.textContent = labelsVisible
                    ? window.SIGAP_I18N[lang].dash_hide_labels
                    : window.SIGAP_I18N[lang].dash_show_labels;
            });

            // Re-sync on lang change
            document.addEventListener('sigap:langchange', function (e) {
                const lang = e.detail.lang;
                mapToggleBtn.textContent = labelsVisible
                    ? window.SIGAP_I18N[lang].dash_hide_labels
                    : window.SIGAP_I18N[lang].dash_show_labels;
            });
        })();
    </script>
@endsection