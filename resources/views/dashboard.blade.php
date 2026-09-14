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
            @else
                <div class="empty-state" style="padding:32px 0;">
                    <div class="empty-state-icon">◷</div>
                    <div class="empty-state-title">Belum ada aktivitas</div>
                    <div class="empty-state-text">Upload file Excel WO untuk melihat aktivitas terbaru.</div>
                </div>
            @endif
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
     REKAPITULASI UPLOAD TERAKHIR
====================================================== --}}
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

        {{-- Stats ringkas --}}
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px;">
            <div style="text-align:center; padding:12px; background:var(--bg-secondary); border-radius:8px;">
                <div style="font-size:22px; font-weight:700; color:var(--text-primary);">{{ $lastUpload->imported_rows }}</div>
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

        {{-- Nama file --}}
        <div style="font-size:12px; color:var(--text-secondary); margin-bottom:16px;">
            <span style="color:var(--text-muted);">File:</span>
            <span style="font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--text-primary);">{{ $lastUpload->filename }}</span>
            <span style="color:var(--text-muted); margin-left:8px;">·</span>
            <span style="color:var(--text-muted); margin-left:8px;">Total {{ $lastUpload->total_rows }} baris</span>
        </div>

        {{-- Distribusi per PLTA --}}
        @if($pltaDistribution && $pltaDistribution->count() > 0)
        <div style="font-size:12px; font-weight:600; color:var(--text-primary); margin-bottom:10px;">Distribusi per PLTA:</div>
        <div style="display:flex; flex-wrap:wrap; gap:8px;" id="plta-distribution-chips">
            @foreach($pltaDistribution as $item)
                <span style="
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

{{-- ======================================================
     INFO PENGGUNAAN SISTEM
====================================================== --}}
<div class="card" id="info-usage-card" style="margin-top:16px;">
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
