@extends('layouts.app')

@section('title', 'Upload Data WO')

@section('page-title', 'Upload Data Work Order')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Upload Data WO</li>
@endsection

@section('content')

    {{-- Flash / upload error --}}
    @if(session('error') || isset($uploadError))
        <div id="upload-flash-error" style="
            display:flex; align-items:flex-start; gap:12px;
            background:#FEF2F2; border:1px solid #FECACA; border-radius:8px;
            padding:14px 18px; margin-bottom:16px;
        ">
            <span style="color:#DC2626; font-size:18px; line-height:1;">⚠</span>
            <div>
                <div style="font-size:13px; font-weight:600; color:#991B1B; margin-bottom:2px;" data-i18n="upload_flash_fail">
                    Gagal Memproses File</div>
                <div style="font-size:12.5px; color:#7F1D1D;">{{ session('error') ?? $uploadError }}</div>
            </div>
        </div>
    @endif

    {{-- ======================================================
    INSTRUKSI WAJIB
    ====================================================== --}}
    <div class="upload-instructions" id="upload-instructions">
        <div class="upload-instructions-title" data-i18n="upload_instr_title">
            ℹ &nbsp;Instruksi Wajib Sebelum Upload
        </div>
        <ul>
            <li data-i18n="upload_instr_1">File Excel yang di-upload <strong>harus sudah difilter secara manual</strong>
                oleh user terlebih dahulu.</li>
            <li>
                <span data-i18n="upload_instr_2">Pastikan file hanya mengandung data WO dengan Worktype yang
                    diizinkan:</span>
                <div class="worktype-tags">
                    <span class="worktype-tag">CM</span>
                    <span class="worktype-tag">EJ</span>
                    <span class="worktype-tag">EV</span>
                    <span class="worktype-tag">PAM</span>
                </div>
            </li>
            <li data-i18n="upload_instr_3">Hapus semua baris dengan Worktype selain keempat di atas sebelum upload.</li>
            <li data-i18n="upload_instr_4">Pastikan file Excel memiliki kolom: <strong>NO WO, DESCRIPTION, WORKTYPE, STATUS,
                    ASSETNUM</strong>.</li>
            <li data-i18n="upload_instr_5">ASSETNUM harus sesuai dengan data master PLTA yang terdaftar dalam sistem.</li>
        </ul>
    </div>

    {{-- ======================================================
    WARNING BOX
    ====================================================== --}}
    <div class="warning-box" id="upload-warning">
        <div class="warning-box-icon">⚠</div>
        <div class="warning-box-text">
            <div class="warning-box-title" data-i18n="upload_warning_title">Perhatian</div>
            <span data-i18n="upload_warning_text">Data yang di-upload akan digunakan sebagai dasar penentuan Status Operasi
                Equipment secara otomatis.
                WO dengan status <strong>APPR / INPRG / PTWCL / PTWR / WPTW</strong> akan menetapkan status
                <strong>ABNORMAL</strong>.
                WO dengan status <strong>CLOSE / COMP</strong> akan menetapkan status <strong>NORMAL</strong>.</span>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; align-items:start;">

        {{-- ======================================================
        FORM UPLOAD
        ====================================================== --}}
        <div>
            <div class="card" id="upload-form-card">
                <div class="card-header">
                    <div class="card-title">
                        <span class="card-title-icon">↑</span>
                        <span data-i18n="upload_card_title">Pilih File Excel</span>
                    </div>
                </div>
                <div class="card-body">

                    <form method="POST" action="{{ route('upload.preview') }}" enctype="multipart/form-data"
                        id="upload-form">
                        @csrf

                        {{-- Dropzone --}}
                        <div class="dropzone" id="dropzone" role="button" tabindex="0" data-i18n-aria="upload_card_title"
                            aria-label="Area upload file Excel">
                            <input type="file" name="excel_file" id="excel-file-input" class="dropzone-file-input"
                                accept=".xlsx,.xls" aria-label="Pilih file Excel">
                            <span class="dropzone-icon" id="dropzone-icon">⬆</span>
                            <div class="dropzone-title" data-i18n="upload_dropzone_title">Klik atau seret file ke sini</div>
                            <div class="dropzone-subtitle" data-i18n="upload_dropzone_subtitle">Format yang diterima: .xlsx,
                                .xls</div>
                            <div>
                                <span class="btn btn-secondary btn-sm" style="pointer-events:none;"
                                    data-i18n="upload_dropzone_btn">Pilih File</span>
                            </div>
                        </div>

                        {{-- File info (muncul setelah file dipilih) --}}
                        <div class="file-selected-info" id="file-selected-info" style="display:none;">
                            <span class="file-icon">📊</span>
                            <div>
                                <div class="file-name" id="file-name-display">—</div>
                                <div class="file-size" id="file-size-display">—</div>
                            </div>
                        </div>

                        {{-- Validasi error dari Laravel --}}
                        @error('excel_file')
                            <div
                                style="font-size:12px; color:#DC2626; margin-top:8px; padding:8px 12px; background:#FEF2F2; border:1px solid #FECACA; border-radius:6px;">
                                ⚠ {{ $message }}
                            </div>
                        @enderror

                        {{-- Actions --}}
                        <div class="upload-actions">
                            <a href="{{ route('dashboard') }}" class="btn btn-danger btn-sm" id="btn-cancel-upload"
                                data-i18n="upload_cancel">
                                ✕ Batalkan
                            </a>
                            <button type="submit" class="btn btn-success btn-sm" id="btn-preview-upload"
                                data-i18n="upload_preview_btn">
                                ↑ Tampilkan Preview
                            </button>
                        </div>

                    </form>

                </div>
            </div>

            {{-- Kolom Referensi --}}
            <div class="card" id="column-reference-card" style="margin-top:16px;">
                <div class="card-header">
                    <div class="card-title">
                        <span class="card-title-icon">≡</span>
                        <span data-i18n="upload_col_ref_title">Kolom yang Diperlukan</span>
                    </div>
                </div>
                <div class="card-body" style="padding:0;">
                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr style="background:#F7F9FC;">
                                <th style="padding:8px 14px; text-align:left; color:var(--text-label); font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid var(--border-color);"
                                    data-i18n="upload_col_name">Nama Kolom</th>
                                <th style="padding:8px 14px; text-align:left; color:var(--text-label); font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid var(--border-color);"
                                    data-i18n="upload_col_desc_label">Keterangan</th>
                                <th style="padding:8px 14px; text-align:center; color:var(--text-label); font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid var(--border-color);"
                                    data-i18n="upload_col_required">Wajib</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom:1px solid #F0F4F8;">
                                <td
                                    style="padding:9px 14px; font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--color-primary);">
                                    NO WO</td>
                                <td style="padding:9px 14px; color:var(--text-secondary);"
                                    data-i18n="upload_col_no_wo_desc">Nomor Work Order unik</td>
                                <td style="padding:9px 14px; text-align:center;"><span
                                        style="color:#059669; font-weight:600; font-size:13px;">✓</span></td>
                            </tr>
                            <tr style="border-bottom:1px solid #F0F4F8;">
                                <td
                                    style="padding:9px 14px; font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--color-primary);">
                                    DESCRIPTION</td>
                                <td style="padding:9px 14px; color:var(--text-secondary);" data-i18n="upload_col_desc_desc">
                                    Deskripsi pekerjaan WO</td>
                                <td style="padding:9px 14px; text-align:center;"><span
                                        style="color:#059669; font-weight:600; font-size:13px;">✓</span></td>
                            </tr>
                            <tr style="border-bottom:1px solid #F0F4F8;">
                                <td
                                    style="padding:9px 14px; font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--color-primary);">
                                    WORKTYPE</td>
                                <td style="padding:9px 14px; color:var(--text-secondary);"
                                    data-i18n="upload_col_worktype_desc">CM / EJ / EV / PAM</td>
                                <td style="padding:9px 14px; text-align:center;"><span
                                        style="color:#059669; font-weight:600; font-size:13px;">✓</span></td>
                            </tr>
                            <tr style="border-bottom:1px solid #F0F4F8;">
                                <td
                                    style="padding:9px 14px; font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--color-primary);">
                                    STATUS</td>
                                <td style="padding:9px 14px; color:var(--text-secondary);"
                                    data-i18n="upload_col_status_desc">APPR / INPRG / CLOSE / COMP / dll</td>
                                <td style="padding:9px 14px; text-align:center;"><span
                                        style="color:#059669; font-weight:600; font-size:13px;">✓</span></td>
                            </tr>
                            <tr style="border-bottom:1px solid #F0F4F8;">
                                <td
                                    style="padding:9px 14px; font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--color-primary);">
                                    ASSETNUM</td>
                                <td style="padding:9px 14px; color:var(--text-secondary);"
                                    data-i18n="upload_col_assetnum_desc">Identifier equipment (contoh: BSGR010078)</td>
                                <td style="padding:9px 14px; text-align:center;"><span
                                        style="color:#059669; font-weight:600; font-size:13px;">✓</span></td>
                            </tr>
                            <tr style="border-bottom:1px solid #F0F4F8;">
                                <td
                                    style="padding:9px 14px; font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--color-primary);">
                                    NAMA ASSET</td>
                                <td style="padding:9px 14px; color:var(--text-secondary);"
                                    data-i18n="upload_col_namaasset_desc">Nama equipment (opsional, diambil dari master jika
                                    kosong)</td>
                                <td style="padding:9px 14px; text-align:center;"><span
                                        style="color:var(--text-muted);">—</span></td>
                            </tr>
                            <tr style="border-bottom:1px solid #F0F4F8;">
                                <td
                                    style="padding:9px 14px; font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--color-primary);">
                                    REPORTDATE</td>
                                <td style="padding:9px 14px; color:var(--text-secondary);"
                                    data-i18n="upload_col_reportdate_desc">Tanggal laporan WO (opsional)</td>
                                <td style="padding:9px 14px; text-align:center;"><span
                                        style="color:var(--text-muted);">—</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ======================================================
        ATURAN STATUS OTOMATIS
        ====================================================== --}}
        <div>
            <div class="card" id="status-rules-card">
                <div class="card-header">
                    <div class="card-title">
                        <span class="card-title-icon">⚙</span>
                        <span data-i18n="upload_status_rules_title">Aturan Penentuan Status Operasi</span>
                    </div>
                </div>
                <div class="card-body">

                    <div style="margin-bottom:16px;">
                        <div
                            style="font-size:12px; font-weight:600; color:var(--status-abnormal-text); margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                            <span
                                style="width:8px; height:8px; background:var(--status-abnormal-dot); border-radius:50%; display:inline-block;"></span>
                            <span data-i18n="status_abnormal_heading">Status ABNORMAL</span>
                        </div>
                        <div
                            style="background:var(--status-abnormal-bg); border:1px solid #FECACA; border-radius:6px; padding:12px 14px;">
                            <div style="font-size:11.5px; color:var(--status-abnormal-text); margin-bottom:6px;">Worktype:
                                <strong>CM / EJ / EV / PAM</strong></div>
                            <div style="font-size:11.5px; color:var(--status-abnormal-text);">Status WO:</div>
                            <div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:5px;">
                                @foreach(['APPR', 'INPRG', 'PTWCL', 'PTWR', 'WPTW'] as $s)
                                    <span
                                        style="background:#DC2626; color:#FFF; font-size:10.5px; font-weight:600; font-family:'JetBrains Mono',monospace; padding:1px 7px; border-radius:3px;">{{ $s }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <div
                            style="font-size:12px; font-weight:600; color:var(--status-normal-text); margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                            <span
                                style="width:8px; height:8px; background:var(--status-normal-dot); border-radius:50%; display:inline-block;"></span>
                            <span data-i18n="status_normal_heading">Status NORMAL</span>
                        </div>
                        <div
                            style="background:var(--status-normal-bg); border:1px solid #A7F3D0; border-radius:6px; padding:12px 14px;">
                            <div style="font-size:11.5px; color:var(--status-normal-text); margin-bottom:6px;">Worktype:
                                <strong>CM / EJ / EV / PAM</strong></div>
                            <div style="font-size:11.5px; color:var(--status-normal-text);">Status WO:</div>
                            <div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:5px;">
                                @foreach(['CLOSE', 'COMP'] as $s)
                                    <span
                                        style="background:#059669; color:#FFF; font-size:10.5px; font-weight:600; font-family:'JetBrains Mono',monospace; padding:1px 7px; border-radius:3px;">{{ $s }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div>
                        <div
                            style="font-size:12px; font-weight:600; color:var(--status-notready-text); margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                            <span
                                style="width:8px; height:8px; background:var(--status-notready-dot); border-radius:50%; display:inline-block;"></span>
                            <span data-i18n="status_not_ready_heading">Status NOT READY</span>
                        </div>
                        <div
                            style="background:var(--status-notready-bg); border:1px solid #FDE68A; border-radius:6px; padding:12px 14px;">
                            <div style="font-size:11.5px; color:var(--status-notready-text);" id="not-ready-desc">
                                Dipilih secara <strong>manual</strong> oleh user melalui dropdown di tabel equipment.
                                Tidak ditentukan otomatis dari data Excel.
                            </div>
                        </div>
                    </div>

                    <div
                        style="background:#F7F9FC; border:1px solid var(--border-color); border-radius:6px; padding:12px 14px; margin-top:16px;">
                        <div style="font-size:11.5px; color:var(--text-secondary); font-weight:600; margin-bottom:4px;"
                            data-i18n="upload_distrib_title">Distribusi PLTA via ASSETNUM</div>
                        <div style="font-size:11.5px; color:var(--text-muted); line-height:1.6;" id="distrib-desc">
                            <span data-i18n="upload_distrib_desc">4 karakter pertama ASSETNUM menentukan PLTA
                                tujuan.</span><br>
                            <span data-i18n="upload_distrib_example">Contoh:</span> <span
                                style="font-family:'JetBrains Mono',monospace; color:var(--color-primary);">BSGR</span>010078
                            <span data-i18n="upload_distrib_arrow">→ PLTA</span> <strong>Sengguruh</strong>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- ======================================================
    PREVIEW DATA (muncul setelah file berhasil diparse)
    ====================================================== --}}
    @if($showPreview)
        <div class="card" id="preview-card" style="margin-top:20px;">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-title-icon">◫</span>
                    <span data-i18n="upload_preview_title">Preview Data Excel</span>
                    <span style="font-size:11px; color:var(--text-muted); font-weight:400; margin-left:4px;">
                        Sheet: {{ $sheetName ?? 'N/A' }} · {{ $originalFilename ?? '' }}
                    </span>
                </div>
                <div class="card-actions">
                    @if(isset($validationResults) && $validationResults['valid_rows'] > 0)
                        <span
                            style="font-size:11.5px; color:#059669; background:#ECFDF5; border:1px solid #A7F3D0; padding:3px 10px; border-radius:100px; font-weight:500;">
                            ✓ {{ number_format($validationResults['valid_rows']) }} <span data-i18n="upload_valid_rows">baris
                                valid</span>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Validasi Summary --}}
            @if(isset($validationResults))
                <div style="margin:16px 16px 0; background:#F0FDF4; border:1px solid #A7F3D0; border-radius:8px; padding:16px;">
                    <div
                        style="font-size:12px; font-weight:700; color:#065F46; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                        <span data-i18n="upload_validation_title">✓ Hasil Validasi File</span>
                    </div>
                    <div style="display:grid; grid-template-columns: repeat(5, 1fr); gap:12px;">
                        <div
                            style="background:white; border:1px solid #D1FAE5; border-radius:6px; padding:10px 12px; text-align:center;">
                            <div style="font-size:18px; font-weight:700; color:var(--text-primary);">
                                {{ number_format($validationResults['total_rows']) }}</div>
                            <div style="font-size:10.5px; color:var(--text-label); margin-top:2px;" data-i18n="upload_total_rows">
                                Total Baris</div>
                        </div>
                        <div
                            style="background:white; border:1px solid #D1FAE5; border-radius:6px; padding:10px 12px; text-align:center;">
                            <div style="font-size:18px; font-weight:700; color:#059669;">
                                {{ number_format($validationResults['valid_rows']) }}</div>
                            <div style="font-size:10.5px; color:var(--text-label); margin-top:2px;" data-i18n="upload_valid">Valid
                            </div>
                        </div>
                        <div
                            style="background:white; border:1px solid #D1FAE5; border-radius:6px; padding:10px 12px; text-align:center;">
                            <div
                                style="font-size:18px; font-weight:700; color:{{ $validationResults['invalid_rows'] > 0 ? '#DC2626' : '#059669' }};">
                                {{ number_format($validationResults['invalid_rows']) }}</div>
                            <div style="font-size:10.5px; color:var(--text-label); margin-top:2px;" data-i18n="upload_invalid">Tidak
                                Valid</div>
                        </div>
                        <div
                            style="background:white; border:1px solid #D1FAE5; border-radius:6px; padding:10px 12px; text-align:center;">
                            <div style="font-size:18px; font-weight:700; color:#2563EB;">
                                {{ number_format($validationResults['new_count']) }}</div>
                            <div style="font-size:10.5px; color:var(--text-label); margin-top:2px;" data-i18n="upload_new_data">Data
                                Baru</div>
                        </div>
                        <div
                            style="background:white; border:1px solid #D1FAE5; border-radius:6px; padding:10px 12px; text-align:center;">
                            <div style="font-size:18px; font-weight:700; color:#D97706;">
                                {{ number_format($validationResults['update_count']) }}</div>
                            <div style="font-size:10.5px; color:var(--text-label); margin-top:2px;" data-i18n="upload_update_data">
                                Data Update</div>
                        </div>
                    </div>
                    @if(count($validationResults['found_plta']) > 0)
                        <div style="margin-top:12px; font-size:11.5px; color:#065F46;">
                            <strong data-i18n="upload_plta_detected">PLTA terdeteksi:</strong>
                            @foreach($validationResults['found_plta'] as $pltaName)
                                <span
                                    style="background:#D1FAE5; border:1px solid #6EE7B7; color:#065F46; font-size:10.5px; padding:1px 8px; border-radius:100px; margin-left:4px; display:inline-block; margin-top:3px;">{{ $pltaName }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Error Rows (jika ada) --}}
            @if(isset($errorRows) && count($errorRows) > 0)
                <div
                    style="margin:12px 16px 0; background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; padding:14px 16px;">
                    <div
                        style="font-size:12px; font-weight:700; color:#991B1B; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                        <span>⚠</span>
                        {{ count($errorRows) }} <span data-i18n="upload_error_rows">baris tidak dapat diproses</span>
                    </div>
                    <div style="max-height:180px; overflow-y:auto;">
                        @foreach($errorRows as $err)
                            <div style="font-size:11.5px; border-bottom:1px solid #FEE2E2; padding:6px 0; display:flex; gap:10px;">
                                <span
                                    style="font-family:'JetBrains Mono',monospace; color:#7F1D1D; font-weight:600; min-width:100px; flex-shrink:0;">
                                    <span data-i18n="upload_row_prefix">Baris</span> {{ $err['row'] }}
                                </span>
                                <span style="font-family:'JetBrains Mono',monospace; color:#991B1B; min-width:110px; flex-shrink:0;">
                                    {{ $err['assetnum'] }}
                                </span>
                                <span style="color:#7F1D1D;">
                                    {{ implode(' · ', $err['errors']) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Tabel Preview --}}
            @if(count($previewData) > 0)
                <div class="table-wrapper" style="margin-top:16px;">
                    <table class="data-table" id="preview-table">
                        <thead>
                            <tr>
                                <th style="width:32px; text-align:center;" data-i18n="upload_th_preview_no">#</th>
                                <th data-i18n="upload_th_assetnum">ASSETNUM</th>
                                <th data-i18n="upload_th_asset_name">Nama Asset</th>
                                <th data-i18n="upload_th_no_wo">No WO</th>
                                <th data-i18n="upload_th_description">Description</th>
                                <th data-i18n="upload_th_worktype">Worktype</th>
                                <th data-i18n="upload_th_wo_status">Status WO</th>
                                <th data-i18n="upload_th_plta">PLTA</th>
                                <th data-i18n="upload_th_op_status">Status Operasi</th>
                                <th data-i18n="upload_th_action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewData as $idx => $row)
                                <tr>
                                    <td style="text-align:center; font-size:11px; color:var(--text-muted);">{{ $row['row'] }}</td>
                                    <td class="col-assetnum">{{ $row['assetnum'] }}</td>
                                    <td style="font-size:12px;">{{ $row['nama_asset'] ?: '—' }}</td>
                                    <td style="font-family:'JetBrains Mono',monospace; font-size:11.5px; color:var(--color-accent);">
                                        {{ $row['no_wo'] }}</td>
                                    <td style="font-size:12px; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                                        title="{{ $row['description'] }}">{{ $row['description'] }}</td>
                                    <td>
                                        <span
                                            style="background:var(--color-primary); color:#FFF; font-size:10.5px; font-weight:600; font-family:'JetBrains Mono',monospace; padding:2px 7px; border-radius:3px;">{{ $row['worktype'] }}</span>
                                    </td>
                                    <td>
                                        <span style="font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:600;
                                            @if(in_array($row['wo_status'], ['APPR', 'INPRG', 'PTWCL', 'PTWR', 'WPTW']))
                                                color:#DC2626; background:#FEF2F2; padding:1px 6px; border-radius:3px; border:1px solid #FECACA;
                                            @else
                                                color:#059669; background:#ECFDF5; padding:1px 6px; border-radius:3px; border:1px solid #A7F3D0;
                                            @endif
                                        ">{{ $row['wo_status'] }}</span>
                                    </td>
                                    <td style="font-size:12px; font-weight:500;">{{ $row['plta_name'] }}</td>
                                    <td>
                                        @php $statusClass = match ($row['status_otomatis']) { 'abnormal' => 'abnormal', 'normal' => 'normal', default => 'not-ready'}; @endphp
                                        @php $statusLabel = match ($row['status_otomatis']) { 'abnormal' => 'Abnormal', 'normal' => 'Normal', default => '—'}; @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>
                                        @if($row['is_new'])
                                            <span
                                                style="background:#EFF6FF; border:1px solid #BFDBFE; color:#1D4ED8; font-size:10px; font-weight:700; padding:1px 7px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">NEW</span>
                                        @else
                                            <span
                                                style="background:#FFFBEB; border:1px solid #FDE68A; color:#92400E; font-size:10px; font-weight:700; padding:1px 7px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">UPDATE</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding:24px; text-align:center; color:var(--text-muted); font-size:13px;" data-i18n="upload_no_valid">
                    Tidak ada data valid untuk ditampilkan.
                </div>
            @endif

            {{-- Confirm/Cancel Actions --}}
            <div
                style="padding:16px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); background:var(--bg-surface-2);">
                <a href="{{ route('upload.index') }}" class="btn btn-danger" id="btn-cancel-confirm"
                    data-i18n="upload_cancel_upload">
                    ✕ Batalkan Upload
                </a>

                @if(count($previewData) > 0)
                    <form method="POST" action="{{ route('upload.commit') }}" id="confirm-form">
                        @csrf
                        <button type="submit" class="btn btn-success" id="btn-confirm-upload" onclick="return handleConfirm(this)">
                            <span data-i18n="upload_confirm">✓ Konfirmasi & Import Data</span>
                            <span
                                style="background:rgba(255,255,255,0.25); border-radius:100px; padding:1px 8px; font-size:11px; margin-left:6px;">
                                {{ number_format($validationResults['valid_rows'] ?? count($previewData)) }} <span
                                    data-i18n="upload_rows_suffix">baris</span>
                            </span>
                        </button>
                    </form>
                @else
                    <span style="font-size:12.5px; color:var(--text-muted);" data-i18n="upload_no_valid_import">Tidak ada data valid
                        untuk diimport.</span>
                @endif
            </div>
        </div>
    @endif

    {{-- ======================================================
    IMPORT LOADING OVERLAY
    ====================================================== --}}
    <div id="import-overlay" style="
        display: none;
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 24px;
    ">
        <div style="
            background: white;
            border-radius: 16px;
            padding: 40px 48px;
            text-align: center;
            box-shadow: 0 24px 64px rgba(0,0,0,0.25);
            max-width: 400px;
            width: 90%;
        ">
            {{-- Spinner --}}
            <div style="
                width: 56px; height: 56px;
                border: 5px solid #E5E7EB;
                border-top-color: var(--color-primary, #1E3A5F);
                border-radius: 50%;
                margin: 0 auto 20px;
                animation: spin 0.9s linear infinite;
            " id="import-spinner"></div>

            <div style="font-size: 16px; font-weight: 700; color: var(--text-primary, #1E293B); margin-bottom: 8px;"
                data-i18n="upload_overlay_title">
                Sedang Memproses Import…
            </div>
            <div id="import-overlay-msg" style="font-size: 13px; color: var(--text-secondary, #64748B); line-height: 1.5;"
                data-i18n="upload_overlay_msg">
                Mohon tunggu, data Work Order sedang diproses dan didistribusikan ke seluruh PLTA.
            </div>

            {{-- Progress hint --}}
            <div style="
                margin-top: 20px;
                background: #F1F5F9;
                border-radius: 100px;
                height: 6px;
                overflow: hidden;
            ">
                <div id="import-progress-bar" style="
                    height: 100%;
                    width: 0%;
                    background: linear-gradient(90deg, var(--color-primary, #1E3A5F), #3B82F6);
                    border-radius: 100px;
                    transition: width 0.4s ease;
                    animation: indeterminate 1.8s ease-in-out infinite;
                "></div>
            </div>

            <div style="margin-top: 14px; font-size: 11px; color: var(--text-muted, #94A3B8);"
                data-i18n="upload_overlay_warning">
                Jangan tutup atau me-refresh halaman ini.
            </div>
        </div>
    </div>

    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes indeterminate {
            0% {
                transform: translateX(-100%);
                width: 50%;
            }

            50% {
                transform: translateX(150%);
                width: 60%;
            }

            100% {
                transform: translateX(350%);
                width: 40%;
            }
        }
    </style>

@endsection

@section('scripts')
    <script>
        // File input display
        const fileInput = document.getElementById('excel-file-input');
        const fileInfo = document.getElementById('file-selected-info');
        const fileNameEl = document.getElementById('file-name-display');
        const fileSizeEl = document.getElementById('file-size-display');
        const dropzone = document.getElementById('dropzone');
        const dropzoneIcon = document.getElementById('dropzone-icon');
        const previewBtn = document.getElementById('btn-preview-upload');

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    fileNameEl.textContent = file.name;
                    const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                    fileSizeEl.textContent = sizeMB + ' MB';
                    fileInfo.style.display = 'flex';
                    dropzoneIcon.textContent = '✓';
                    dropzone.style.borderColor = '#10B981';
                    dropzone.style.background = '#ECFDF5';
                }
            });
        }

        // Drag & drop
        if (dropzone) {
            dropzone.addEventListener('click', function () {
                fileInput && fileInput.click();
            });
            dropzone.addEventListener('dragover', function (e) {
                e.preventDefault();
                this.classList.add('dragover');
            });
            dropzone.addEventListener('dragleave', function () {
                this.classList.remove('dragover');
            });
            dropzone.addEventListener('drop', function (e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0 && fileInput) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(files[0]);
                    fileInput.files = dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        }

        // Loading saat preview
        const uploadForm = document.getElementById('upload-form');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function () {
                if (previewBtn) {
                    previewBtn.textContent = window.SIGAP_T ? window.SIGAP_T('upload_loading') : '⏳ Membaca file...';
                    previewBtn.disabled = true;
                }
            });
        }

        // Confirm & Import — tampilkan overlay loading, cegah double-submit
        let importSubmitting = false;

        function handleConfirm(btn) {
            if (importSubmitting) {
                return false;
            }
            const confirmMsg = window.SIGAP_T ? window.SIGAP_T('upload_confirm_dialog') : 'Apakah Anda yakin ingin mengimport data ini ke database?\n\nProses ini tidak dapat dibatalkan setelah dikonfirmasi.';
            if (!confirm(confirmMsg)) {
                return false;
            }

            importSubmitting = true;

            const overlay = document.getElementById('import-overlay');
            if (overlay) {
                overlay.style.display = 'flex';
            }

            const rowCount = {{ $validationResults['valid_rows'] ?? count($previewData ?? []) }};
            const msgEl = document.getElementById('import-overlay-msg');
            if (msgEl && rowCount > 0) {
                const processingPrefix = window.SIGAP_T ? window.SIGAP_T('upload_processing') : 'Memproses';
                const processingSuffix = window.SIGAP_T ? window.SIGAP_T('upload_processing_suffix') : 'baris data Work Order…';
                msgEl.textContent = processingPrefix + ' ' + rowCount.toLocaleString() + ' ' + processingSuffix;
            }

            setTimeout(function () {
                btn.disabled = true;
                btn.style.opacity = '0.6';
            }, 0);

            return true;
        }

        // Jika user menekan tombol Back dari halaman result, reset flag
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                importSubmitting = false;
                const overlay = document.getElementById('import-overlay');
                if (overlay) { overlay.style.display = 'none'; }
            }
        });

        // Not-ready description — update on lang change
        const notReadyDesc = document.getElementById('not-ready-desc');
        if (notReadyDesc) {
            document.addEventListener('sigap:langchange', function (e) {
                const lang = e.detail.lang;
                if (lang === 'en') {
                    notReadyDesc.innerHTML = 'Chosen <strong>manually</strong> by the user via the dropdown in the equipment table. Not automatically determined from Excel data.';
                } else {
                    notReadyDesc.innerHTML = 'Dipilih secara <strong>manual</strong> oleh user melalui dropdown di tabel equipment. Tidak ditentukan otomatis dari data Excel.';
                }
            });
        }

        // Scroll ke preview jika ada
        @if($showPreview)
            setTimeout(function () {
                const previewCard = document.getElementById('preview-card');
                if (previewCard) {
                    previewCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 200);
        @endif
    </script>
@endsection