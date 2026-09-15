@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item">SIGAP</li>
    <li class="breadcrumb-item">Dashboard</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
@endpush

@section('content')

    <div class="stats-grid" id="stats-overview">

        <div class="stat-card">
            <div class="stat-icon blue">⚡</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="total_plta">{{ $stats['total_plta'] }}</div>
                <div class="stat-label">Total PLTA</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">⚙</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="total_equipment">{{ $stats['total_equipment'] }}</div>
                <div class="stat-label">Total Equipment</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">✓</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="normal">{{ $stats['normal'] }}</div>
                <div class="stat-label">Status Normal</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">!</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="abnormal">{{ $stats['abnormal'] }}</div>
                <div class="stat-label">Status Abnormal</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon amber">⏸</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="not_ready">{{ $stats['not_ready'] }}</div>
                <div class="stat-label">Not Ready</div>
            </div>
        </div>

    </div>

    <!--  PETA SEBARAN 13 PLTA (SATELLITE VIEW) -->

    <div class="card map-card" id="plta-map-card">
        <div class="card-header">
            <div class="card-title">
                <span class="card-title-icon">📍</span>
                Peta Sebaran PLTA UP Brantas
            </div>
            <div class="map-toolbar">
                <div class="map-legend">
                    <span class="map-legend-item"><span class="map-legend-dot normal"></span> Normal</span>
                    <span class="map-legend-item"><span class="map-legend-dot not_ready"></span> Not Ready</span>
                    <span class="map-legend-item"><span class="map-legend-dot abnormal"></span> Abnormal</span>
                </div>
                <button type="button" class="map-toggle-btn" id="map-toggle-labels">Tampilkan Semua Label</button>
            </div>
        </div>
        <div class="map-wrapper">
            <div id="plta-map" role="img" aria-label="Peta sebaran lokasi 13 PLTA UP Brantas"></div>
        </div>
    </div>

    <!-- AKTIVITAS PLTA UPDATE  -->

    <div class="dashboard-grid">

        <div class="card" id="recent-activity-card">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-title-icon">◷</span>
                    Aktivitas Status Terbaru
                </div>
                <div class="card-actions">
                    <span style="font-size:11px; color:var(--text-muted)">Update: {{ date('d M Y') }}</span>
                </div>
            </div>
            <div class="table-wrapper">
                @if(count($recentActivity) > 0)
                    <table class="activity-table" id="activity-table">
                        <thead>
                            <tr>
                                <th>Waktu Upload</th>
                                <th>PLTA</th>
                                <th>ASSETNUM</th>
                                <th>Status</th>
                                <th>No WO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivity as $activity)
                                <tr>
                                    <td><span class="activity-time">{{ $activity['time'] }}</span></td>
                                    <td style="font-size:12.5px; font-weight:500;">{{ $activity['plta'] }}</td>
                                    <td><span class="activity-assetnum">{{ $activity['assetnum'] }}</span></td>
                                    <td>
                                        @php
                                            $statusClass = match ($activity['status']) {
                                                'Normal' => 'normal',
                                                'Abnormal' => 'abnormal',
                                                default => 'not-ready',
                                            };
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ $activity['status'] }}</span>
                                    </td>
                                    <td class="activity-wo">
                                        {{ $activity['wo'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state" style="padding:32px 0;">
                        <div class="empty-state-icon">◷</div>
                        <div class="empty-state-title">Belum ada aktivitas</div>
                        <div class="empty-state-text">Upload file Excel WO untuk melihat aktivitas terbaru.</div>
                    </div>
                @endif
            </div>
        </div>

        <!--        REKAPITULASI UPLOAD TERAKHIR -->
        @if($lastUpload)
            <div class="card" id="last-upload-card" style="margin-top:16px;">
                <div class="card-header">
                    <div class="card-title">
                        <span class="card-title-icon">📊</span>
                        Rekapitulasi Upload Terakhir
                    </div>
                    <div class="card-actions">
                        <span style="font-size:11px; color:var(--text-muted)">
                            {{ $lastUpload->uploaded_at?->format('d M Y, H:i') ?? '—' }}
                            @if($lastUpload->user)
                                · oleh {{ $lastUpload->user->name }}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Stats ringkas -->
                    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px;">
                        <div style="text-align:center; padding:12px; background:var(--bg-secondary); border-radius:8px;">
                            <div style="font-size:22px; font-weight:700; color:var(--text-primary);">
                                {{ $lastUpload->imported_rows }}
                            </div>
                            <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">Baris Diimport</div>
                        </div>
                        <div style="text-align:center; padding:12px; background:var(--bg-secondary); border-radius:8px;">
                            <div style="font-size:22px; font-weight:700; color:#4ade80;">{{ $lastUpload->new_rows }}</div>
                            <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">Data Baru</div>
                        </div>
                        <div style="text-align:center; padding:12px; background:var(--bg-secondary); border-radius:8px;">
                            <div style="font-size:22px; font-weight:700; color:#60a5fa;">{{ $lastUpload->updated_rows }}</div>
                            <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">Diperbarui</div>
                        </div>
                        <div style="text-align:center; padding:12px; background:var(--bg-secondary); border-radius:8px;">
                            <div style="font-size:22px; font-weight:700; color:#f87171;">{{ $lastUpload->error_rows }}</div>
                            <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">Error / Ditolak</div>
                        </div>
                    </div>

                    <!-- Nama file -->
                    <div style="font-size:12px; color:var(--text-secondary); margin-bottom:16px;">
                        <span style="color:var(--text-muted);">File:</span>
                        <span
                            style="font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--text-primary);">{{ $lastUpload->filename }}</span>
                        <span style="color:var(--text-muted); margin-left:8px;">·</span>
                        <span style="color:var(--text-muted); margin-left:8px;">Total {{ $lastUpload->total_rows }} baris</span>
                    </div>

                    <!-- Distribusi per PLTA -->
                    @if($pltaDistribution && $pltaDistribution->count() > 0)
                        <div style="font-size:12px; font-weight:600; color:var(--text-primary); margin-bottom:10px;">Distribusi per
                            PLTA:</div>
                        <div style="display:flex; flex-wrap:wrap; gap:8px;" id="plta-distribution-chips">
                            @foreach($pltaDistribution as $item)
                                <span
                                    style="
                                                                                                                                                    display:inline-flex; align-items:center; gap:6px;
                                                                                                                                                    padding:5px 12px; border-radius:999px;
                                                                                                                                                    background:var(--bg-secondary); border:1px solid var(--border-color);
                                                                                                                                                    font-size:11.5px; color:var(--text-secondary);
                                                                                                                                                ">
                                    <span style="font-weight:600; color:var(--text-primary);">{{ $item['count'] }}</span>
                                    {{ str_replace('PLTA ', '', $item['name']) }}
                                </span>
                            @endforeach
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
    @endsection