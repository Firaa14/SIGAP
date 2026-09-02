@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item">SIGAP</li>
    <li class="breadcrumb-item">Dashboard</li>
@endsection

@section('content')

{{-- ======================================================
     STATISTIK CARDS
====================================================== --}}
<div class="stats-grid" id="stats-overview">

    <div class="stat-card">
        <div class="stat-icon blue">⚡</div>
        <div class="stat-body">
            <div class="stat-value">{{ $stats['total_plta'] }}</div>
            <div class="stat-label">Total PLTA</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">⚙</div>
        <div class="stat-body">
            <div class="stat-value">{{ $stats['total_equipment'] }}</div>
            <div class="stat-label">Total Equipment</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">✓</div>
        <div class="stat-body">
            <div class="stat-value">{{ $stats['normal'] }}</div>
            <div class="stat-label">Status Normal</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red">!</div>
        <div class="stat-body">
            <div class="stat-value">{{ $stats['abnormal'] }}</div>
            <div class="stat-label">Status Abnormal</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">⏸</div>
        <div class="stat-body">
            <div class="stat-value">{{ $stats['not_ready'] }}</div>
            <div class="stat-label">Not Ready</div>
        </div>
    </div>

</div>

{{-- ======================================================
     GRID: AKTIVITAS + DAFTAR PLTA
====================================================== --}}
<div class="dashboard-grid">

    {{-- Aktivitas Terbaru --}}
    <div class="card" id="recent-activity-card">
        <div class="card-header">
            <div class="card-title">
                <span class="card-title-icon">◷</span>
                Aktivitas Status Terbaru
            </div>
            <div class="card-actions">
                <span style="font-size:11px; color:var(--text-muted)">Hari ini · {{ date('d M Y') }}</span>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="activity-table" id="activity-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
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
                                $statusClass = match($activity['status']) {
                                    'Normal'   => 'normal',
                                    'Abnormal' => 'abnormal',
                                    default    => 'not-ready',
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $activity['status'] }}</span>
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace; font-size:11.5px; color:var(--text-muted);">{{ $activity['wo'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daftar PLTA --}}
    <div class="card" id="plta-list-card">
        <div class="card-header">
            <div class="card-title">
                <span class="card-title-icon">⚡</span>
                Daftar 13 PLTA
            </div>
        </div>
        <div class="plta-list-card">
            @foreach($pltaList as $plta)
            <a href="{{ route('plta.show', $plta['slug']) }}" class="plta-list-item" id="dashboard-plta-{{ $plta['slug'] }}">
                <span class="plta-list-code">{{ $plta['code'] }}</span>
                <span class="plta-list-name">{{ str_replace('PLTA ', '', $plta['name']) }}</span>
                <span class="plta-list-capacity">{{ $plta['capacity'] }}</span>
                <span class="plta-list-chevron">›</span>
            </a>
            @endforeach
        </div>
    </div>

</div>

{{-- ======================================================
     INFO PENGGUNAAN SISTEM
====================================================== --}}
<div class="card mt-16" id="info-usage-card" style="margin-top:16px;">
    <div class="card-header">
        <div class="card-title">
            <span class="card-title-icon">ℹ</span>
            Panduan Penggunaan Sistem
        </div>
    </div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px;">
            <div>
                <div style="font-size:12.5px; font-weight:600; color:var(--text-primary); margin-bottom:6px;">1. Pilih PLTA</div>
                <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">
                    Pilih salah satu dari 13 PLTA di sidebar kiri untuk melihat data equipment lengkap.
                </p>
            </div>
            <div>
                <div style="font-size:12.5px; font-weight:600; color:var(--text-primary); margin-bottom:6px;">2. Monitor Status</div>
                <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">
                    Setiap equipment memiliki Status Operasi: <strong>Normal</strong>, <strong>Abnormal</strong>, atau <strong>Not Ready</strong>.
                </p>
            </div>
            <div>
                <div style="font-size:12.5px; font-weight:600; color:var(--text-primary); margin-bottom:6px;">3. Upload Data WO</div>
                <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">
                    Upload file Excel WO melalui menu <em>Upload Data WO</em>. Pastikan data sudah difilter sebelum upload.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
