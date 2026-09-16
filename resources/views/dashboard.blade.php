@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item">SIGAP</li>
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
    </style>
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

        <a href="{{ route('status.index', ['status' => 'normal']) }}" class="stat-card">
            <div class="stat-icon green">✓</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="normal">{{ $stats['normal'] }}</div>
                <div class="stat-label">Status Normal</div>
            </div>
        </a>

        <a href="{{ route('status.index', ['status' => 'abnormal']) }}" class="stat-card">
            <div class="stat-icon red">!</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="abnormal">{{ $stats['abnormal'] }}</div>
                <div class="stat-label">Status Abnormal</div>
            </div>
        </a>

        <div class="stat-card">
            <div class="stat-icon amber">⏸</div>
            <div class="stat-body">
                <div class="stat-value" data-stat="not_ready">{{ $stats['not_ready'] }}</div>
                <div class="stat-label">Not Ready</div>
            </div>
        </div>

    </div>

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

    <div class="card" id="recent-activity-card" style="margin-top:16px;">
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
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Waktu Upload</th>
                            <th>PLTA</th>
                            <th>ASSETNUM</th>
                            <th>Status</th>
                            <th>No WO</th>
                            <th>Report Date</th>
                            <th>Durasi</th>
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
                                <td style="font-size:12.5px;">{{ $activity['total_durasi'] }}</td>
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

@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        window.SIGAP_MAP_DATA = @json($mapData);
        window.SIGAP_MAP_DATA_URL = @json(route('dashboard.map-data'));
    </script>
    <script src="{{ asset('js/dashboard-map.js') }}"></script>
@endsection