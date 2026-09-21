@extends('layouts.app')

@section('title', 'Import Berhasil')

@section('page-title', 'Hasil Import Data WO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('upload.index') }}">Upload Data WO</a></li>
    <li class="breadcrumb-item">Hasil Import</li>
@endsection

@section('content')

{{-- ======================================================
     SUCCESS BANNER
====================================================== --}}
<div style="
    display:flex; align-items:center; gap:16px;
    background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
    border:1px solid #6EE7B7; border-radius:12px;
    padding:20px 24px; margin-bottom:20px;
">
    <div style="
        width:52px; height:52px; border-radius:50%;
        background:linear-gradient(135deg, #10B981, #059669);
        display:flex; align-items:center; justify-content:center;
        font-size:24px; color:white; flex-shrink:0;
        box-shadow:0 4px 12px rgba(16,185,129,0.3);
    ">✓</div>
    <div>
        <div style="font-size:17px; font-weight:700; color:#065F46; margin-bottom:3px;" data-i18n="result_success_title">
            Data Work Order Berhasil Diimport
        </div>
        <div style="font-size:12.5px; color:#047857;">
            <strong>{{ $history->filename }}</strong> <span data-i18n="result_success_desc">telah diproses dan data tersebar ke seluruh PLTA yang relevan.</span>
        </div>
    </div>
    <div style="margin-left:auto; display:flex; gap:10px; flex-shrink:0;">
        <a href="{{ route('upload.index') }}" class="btn btn-secondary btn-sm" id="btn-upload-again" data-i18n="result_upload_again">
            ↑ Upload Lagi
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-success btn-sm" id="btn-view-dashboard" data-i18n="result_view_dashboard">
            ⊞ View Dashboard
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start;">

    {{-- ======================================================
         SUMMARY IMPORT
    ====================================================== --}}
    <div>
        <div class="card" id="result-summary-card">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-title-icon">≡</span>
                    <span data-i18n="result_summary_title">Ringkasan Import</span>
                </div>
            </div>
            <div class="card-body">

                {{-- File info --}}
                <div style="background:#F7F9FC; border:1px solid var(--border-color); border-radius:8px; padding:12px 14px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:12px; font-weight:600; color:var(--text-primary); margin-bottom:2px;">
                                📊 {{ $history->filename }}
                            </div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                <span data-i18n="result_uploaded_at">Diupload</span> {{ $history->uploaded_at?->format('d M Y, H:i') ?? '—' }}
                            </div>
                        </div>
                        <span style="background:#D1FAE5; border:1px solid #6EE7B7; color:#065F46; font-size:11px; font-weight:600; padding:2px 10px; border-radius:100px;" data-i18n="result_done_badge">
                            SELESAI
                        </span>
                    </div>
                </div>

                {{-- Stats grid --}}
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; margin-bottom:16px;">
                    <div style="background:#F7F9FC; border:1px solid var(--border-color); border-radius:8px; padding:12px; text-align:center;">
                        <div style="font-size:22px; font-weight:700; color:var(--text-primary);">{{ number_format($history->total_rows) }}</div>
                        <div style="font-size:10.5px; color:var(--text-label); margin-top:3px; line-height:1.3;" data-i18n="result_total_rows">Total Baris</div>
                    </div>
                    <div style="background:#F0FDF4; border:1px solid #A7F3D0; border-radius:8px; padding:12px; text-align:center;">
                        <div style="font-size:22px; font-weight:700; color:#059669;">{{ number_format($history->imported_rows) }}</div>
                        <div style="font-size:10.5px; color:var(--text-label); margin-top:3px; line-height:1.3;" data-i18n="result_success_rows">Berhasil Diproses</div>
                    </div>
                    <div style="background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; padding:12px; text-align:center;">
                        <div style="font-size:22px; font-weight:700; color:{{ $history->error_rows > 0 ? '#DC2626' : '#059669' }};">{{ number_format($history->error_rows) }}</div>
                        <div style="font-size:10.5px; color:var(--text-label); margin-top:3px; line-height:1.3;" data-i18n="result_failed_rows">Gagal Validasi</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:12px; text-align:center;">
                        <div style="font-size:20px; font-weight:700; color:#2563EB;">{{ number_format($history->new_rows) }}</div>
                        <div style="font-size:10.5px; color:var(--text-label); margin-top:3px; line-height:1.3;" data-i18n="result_new_rows">Data Baru (INSERT)</div>
                    </div>
                    <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:8px; padding:12px; text-align:center;">
                        <div style="font-size:20px; font-weight:700; color:#D97706;">{{ number_format($history->updated_rows) }}</div>
                        <div style="font-size:10.5px; color:var(--text-label); margin-top:3px; line-height:1.3;" data-i18n="result_updated_rows">Data Update</div>
                    </div>
                </div>

                {{-- Status note --}}
                <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:6px; padding:10px 12px; margin-top:14px; font-size:11.5px; color:#92400E;">
                    <strong data-i18n="result_note_label">Catatan:</strong> <span data-i18n="result_note_text">Status operasi equipment telah diperbarui secara otomatis berdasarkan data WO yang diimport.</span>
                </div>

            </div>
        </div>
    </div>

    {{-- ======================================================
         DISTRIBUSI PER PLTA
    ====================================================== --}}
    <div>
        <div class="card" id="result-distribution-card">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-title-icon">◎</span>
                    <span data-i18n="result_distrib_title">Distribusi Data ke PLTA</span>
                </div>
                <div class="card-actions">
                    <span style="font-size:11.5px; color:var(--text-muted);">
                        {{ $pltaDistribution->where('count', '>', 0)->count() }}
                        <span data-i18n="result_distrib_affected">dari</span>
                        {{ $pltaDistribution->count() }}
                        <span data-i18n="result_distrib_affected2">PLTA terdampak</span>
                    </span>
                </div>
            </div>
            <div class="card-body" style="padding:0;">

                @if($pltaDistribution->isEmpty())
                <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:12.5px;" data-i18n="result_no_distrib">
                    Tidak ada distribusi PLTA tercatat.
                </div>
                @else
                @php $maxCount = $pltaDistribution->max('count'); @endphp
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F7F9FC;">
                            <th style="padding:8px 16px; text-align:left; font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; color:var(--text-label); border-bottom:1px solid var(--border-color);" data-i18n="result_th_plta">PLTA</th>
                            <th style="padding:8px 16px; text-align:right; font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; color:var(--text-label); border-bottom:1px solid var(--border-color);" data-i18n="result_th_wo_count">Jumlah WO</th>
                            <th style="padding:8px 16px; border-bottom:1px solid var(--border-color); width:120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pltaDistribution as $item)
                        @php
                            $pltaSlug  = collect($pltaList)->firstWhere('name', $item['name'])['slug'] ?? null;
                            $barWidth  = ($maxCount > 0 && $item['count'] > 0) ? round(($item['count'] / $maxCount) * 100) : 0;
                            $hasData   = $item['count'] > 0;
                        @endphp
                        <tr style="border-bottom:1px solid #F0F4F8; {{ $hasData ? '' : 'opacity:0.45;' }}">
                            <td style="padding:10px 16px;">
                                @if($pltaSlug && $hasData)
                                <a href="{{ route('plta.show', $pltaSlug) }}"
                                   style="font-size:12.5px; font-weight:500; color:var(--color-primary); text-decoration:none;">
                                    {{ str_replace('PLTA ', '', $item['name']) }}
                                </a>
                                @else
                                <span style="font-size:12.5px; font-weight:500; color:var(--text-primary);">
                                    {{ str_replace('PLTA ', '', $item['name']) }}
                                </span>
                                @endif
                            </td>
                            <td style="padding:10px 16px; text-align:right; font-size:13px; font-weight:700; color:{{ $hasData ? 'var(--color-primary)' : 'var(--text-muted)' }};">
                                {{ $hasData ? number_format($item['count']) : '—' }}
                            </td>
                            <td style="padding:10px 16px;">
                                <div style="background:#E5E7EB; border-radius:100px; height:6px; overflow:hidden;">
                                    <div style="background:var(--color-primary); height:100%; width:{{ $barWidth }}%; border-radius:100px; transition:width 0.5s ease;"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

            </div>
        </div>
    </div>

</div>

{{-- ======================================================
     QUICK ACTIONS
====================================================== --}}
<div style="margin-top:20px; display:flex; justify-content:center; gap:12px;">
    <a href="{{ route('upload.index') }}" class="btn btn-secondary" id="btn-upload-another" data-i18n="result_upload_another">
        ↑ Upload File Lain
    </a>
    <a href="{{ route('dashboard') }}" class="btn btn-success" id="btn-go-dashboard" data-i18n="result_go_dashboard">
        ⊞ Lihat Dashboard PLTA
    </a>
</div>

@endsection
