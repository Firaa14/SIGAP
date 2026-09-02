@extends('layouts.app')

@section('title', 'Upload Data WO')

@section('page-title', 'Upload Data Work Order')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Upload Data WO</li>
@endsection

@section('content')

{{-- ======================================================
     INSTRUKSI WAJIB
====================================================== --}}
<div class="upload-instructions" id="upload-instructions">
    <div class="upload-instructions-title">
        ℹ &nbsp;Instruksi Wajib Sebelum Upload
    </div>
    <ul>
        <li>File Excel yang di-upload <strong>harus sudah difilter secara manual</strong> oleh user terlebih dahulu.</li>
        <li>Pastikan file hanya mengandung data WO dengan Worktype yang diizinkan:
            <div class="worktype-tags">
                <span class="worktype-tag">CM</span>
                <span class="worktype-tag">EJ</span>
                <span class="worktype-tag">EV</span>
                <span class="worktype-tag">PAM</span>
            </div>
        </li>
        <li>Hapus semua baris dengan Worktype selain keempat di atas sebelum upload.</li>
        <li>Pastikan file Excel memiliki kolom: <strong>NO WO, DESCRIPTION, WORKTYPE, STATUS, ASSETNUM</strong>.</li>
        <li>ASSETNUM harus sesuai dengan data master PLTA yang terdaftar dalam sistem.</li>
    </ul>
</div>

{{-- ======================================================
     WARNING BOX
====================================================== --}}
<div class="warning-box" id="upload-warning">
    <div class="warning-box-icon">⚠</div>
    <div class="warning-box-text">
        <div class="warning-box-title">Perhatian</div>
        Data yang di-upload akan digunakan sebagai dasar penentuan Status Operasi Equipment secara otomatis.
        WO dengan status <strong>APPR / INPRG / PTWCL / PTWR / WPTW</strong> akan menetapkan status <strong>ABNORMAL</strong>.
        WO dengan status <strong>CLOSE / COMP</strong> akan menetapkan status <strong>NORMAL</strong>.
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
                    Pilih File Excel
                </div>
            </div>
            <div class="card-body">

                <form method="POST" action="{{ route('upload.preview') }}" enctype="multipart/form-data" id="upload-form">
                    @csrf

                    {{-- Dropzone --}}
                    <div class="dropzone" id="dropzone" role="button" tabindex="0" aria-label="Area upload file Excel">
                        <input type="file"
                               name="excel_file"
                               id="excel-file-input"
                               class="dropzone-file-input"
                               accept=".xlsx,.xls,.csv"
                               aria-label="Pilih file Excel">
                        <span class="dropzone-icon" id="dropzone-icon">⬆</span>
                        <div class="dropzone-title">Klik atau seret file ke sini</div>
                        <div class="dropzone-subtitle">Format yang diterima: .xlsx, .xls, .csv</div>
                        <div>
                            <span class="btn btn-secondary btn-sm" style="pointer-events:none;">Pilih File</span>
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

                    {{-- Actions --}}
                    <div class="upload-actions">
                        <a href="{{ route('dashboard') }}" class="btn btn-danger btn-sm" id="btn-cancel-upload">
                            ✕ Batalkan
                        </a>
                        <button type="submit" class="btn btn-success btn-sm" id="btn-preview-upload">
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
                    Kolom yang Diperlukan
                </div>
            </div>
            <div class="card-body" style="padding:0;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="background:#F7F9FC;">
                            <th style="padding:8px 14px; text-align:left; color:var(--text-label); font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid var(--border-color);">Nama Kolom</th>
                            <th style="padding:8px 14px; text-align:left; color:var(--text-label); font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid var(--border-color);">Keterangan</th>
                            <th style="padding:8px 14px; text-align:center; color:var(--text-label); font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid var(--border-color);">Wajib</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['NO WO',       'Nomor Work Order unik', true],
                            ['DESCRIPTION', 'Deskripsi pekerjaan WO', true],
                            ['WORKTYPE',    'CM / EJ / EV / PAM', true],
                            ['STATUS',      'APPR / INPRG / CLOSE / COMP / dll', true],
                            ['ASSETNUM',    'Identifier equipment (contoh: BSGR010078)', true],
                        ] as [$col, $desc, $required])
                        <tr style="border-bottom:1px solid #F0F4F8;">
                            <td style="padding:9px 14px; font-family:'JetBrains Mono',monospace; font-weight:500; color:var(--color-primary);">{{ $col }}</td>
                            <td style="padding:9px 14px; color:var(--text-secondary);">{{ $desc }}</td>
                            <td style="padding:9px 14px; text-align:center;">
                                @if($required)
                                    <span style="color:#059669; font-weight:600; font-size:13px;">✓</span>
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
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
                    Aturan Penentuan Status Operasi
                </div>
            </div>
            <div class="card-body">

                <div style="margin-bottom:16px;">
                    <div style="font-size:12px; font-weight:600; color:var(--status-abnormal-text); margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <span style="width:8px; height:8px; background:var(--status-abnormal-dot); border-radius:50%; display:inline-block;"></span>
                        Status ABNORMAL
                    </div>
                    <div style="background:var(--status-abnormal-bg); border:1px solid #FECACA; border-radius:6px; padding:12px 14px;">
                        <div style="font-size:11.5px; color:var(--status-abnormal-text); margin-bottom:6px;">Worktype: <strong>CM / EJ / EV / PAM</strong></div>
                        <div style="font-size:11.5px; color:var(--status-abnormal-text);">Status WO:</div>
                        <div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:5px;">
                            @foreach(['APPR','INPRG','PTWCL','PTWR','WPTW'] as $s)
                                <span style="background:#DC2626; color:#FFF; font-size:10.5px; font-weight:600; font-family:'JetBrains Mono',monospace; padding:1px 7px; border-radius:3px;">{{ $s }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <div style="font-size:12px; font-weight:600; color:var(--status-normal-text); margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <span style="width:8px; height:8px; background:var(--status-normal-dot); border-radius:50%; display:inline-block;"></span>
                        Status NORMAL
                    </div>
                    <div style="background:var(--status-normal-bg); border:1px solid #A7F3D0; border-radius:6px; padding:12px 14px;">
                        <div style="font-size:11.5px; color:var(--status-normal-text); margin-bottom:6px;">Worktype: <strong>CM / EJ / EV / PAM</strong></div>
                        <div style="font-size:11.5px; color:var(--status-normal-text);">Status WO:</div>
                        <div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:5px;">
                            @foreach(['CLOSE','COMP'] as $s)
                                <span style="background:#059669; color:#FFF; font-size:10.5px; font-weight:600; font-family:'JetBrains Mono',monospace; padding:1px 7px; border-radius:3px;">{{ $s }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <div style="font-size:12px; font-weight:600; color:var(--status-notready-text); margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <span style="width:8px; height:8px; background:var(--status-notready-dot); border-radius:50%; display:inline-block;"></span>
                        Status NOT READY
                    </div>
                    <div style="background:var(--status-notready-bg); border:1px solid #FDE68A; border-radius:6px; padding:12px 14px;">
                        <div style="font-size:11.5px; color:var(--status-notready-text);">
                            Dipilih secara <strong>manual</strong> oleh user melalui dropdown di tabel equipment.
                            Tidak ditentukan otomatis dari data Excel.
                        </div>
                    </div>
                </div>

                <div style="background:#F7F9FC; border:1px solid var(--border-color); border-radius:6px; padding:12px 14px; margin-top:16px;">
                    <div style="font-size:11.5px; color:var(--text-secondary); font-weight:600; margin-bottom:4px;">Distribusi PLTA via ASSETNUM</div>
                    <div style="font-size:11.5px; color:var(--text-muted); line-height:1.6;">
                        4 karakter pertama ASSETNUM menentukan PLTA tujuan.<br>
                        Contoh: <span style="font-family:'JetBrains Mono',monospace; color:var(--color-primary);">BSGR</span>010078 → PLTA <strong>Sengguruh</strong>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ======================================================
     PREVIEW DATA (muncul setelah submit form)
====================================================== --}}
@if($showPreview && count($previewData) > 0)
<div class="card" id="preview-card" style="margin-top:20px;">
    <div class="card-header">
        <div class="card-title">
            <span class="card-title-icon">◫</span>
            Preview Data Excel
            <span style="font-size:11px; color:var(--text-muted); font-weight:400; margin-left:4px;">(Dummy Data — Simulasi)</span>
        </div>
        <div class="card-actions">
            {{-- Validasi Result --}}
            @if(isset($validationResults))
            <span style="font-size:11.5px; color:#059669; background:#ECFDF5; border:1px solid #A7F3D0; padding:3px 10px; border-radius:100px; font-weight:500;">
                ✓ {{ $validationResults['total_rows'] }} baris valid
            </span>
            @endif
        </div>
    </div>

    {{-- Validasi Summary --}}
    @if(isset($validationResults))
    <div class="validation-result" style="margin:16px 16px 0; border-radius:6px;">
        <div class="validation-result-title">✓ Hasil Validasi Data</div>
        <div class="validation-grid">
            <div class="validation-item">
                <div class="validation-item-label">Total Baris</div>
                <div class="validation-item-value">{{ $validationResults['total_rows'] }}</div>
            </div>
            <div class="validation-item">
                <div class="validation-item-label">Baris Valid</div>
                <div class="validation-item-value" style="color:#059669;">{{ $validationResults['valid_rows'] }}</div>
            </div>
            <div class="validation-item">
                <div class="validation-item-label">Baris Tidak Valid</div>
                <div class="validation-item-value" style="color:{{ $validationResults['invalid_rows'] > 0 ? '#DC2626' : '#059669' }};">{{ $validationResults['invalid_rows'] }}</div>
            </div>
        </div>
        <div style="margin-top:10px; font-size:11.5px; color:#065F46;">
            <strong>PLTA terdeteksi:</strong>
            @foreach($validationResults['found_plta'] as $pltaName)
                <span style="background:#D1FAE5; border:1px solid #6EE7B7; color:#065F46; font-size:10.5px; padding:1px 7px; border-radius:100px; margin-left:4px;">{{ $pltaName }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tabel Preview --}}
    <div class="table-wrapper" style="margin-top:16px;">
        <table class="data-table" id="preview-table">
            <thead>
                <tr>
                    <th>ASSETNUM</th>
                    <th>No WO</th>
                    <th>Description</th>
                    <th>Worktype</th>
                    <th>Status WO</th>
                    <th>PLTA</th>
                    <th>Status Operasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($previewData as $row)
                <tr>
                    <td class="col-assetnum">{{ $row['assetnum'] }}</td>
                    <td style="font-family:'JetBrains Mono',monospace; font-size:11.5px; color:var(--color-accent);">{{ $row['no_wo'] }}</td>
                    <td style="font-size:12.5px;">{{ $row['description'] }}</td>
                    <td>
                        <span style="background:var(--color-primary); color:#FFF; font-size:10.5px; font-weight:600; font-family:'JetBrains Mono',monospace; padding:2px 7px; border-radius:3px;">{{ $row['worktype'] }}</span>
                    </td>
                    <td>
                        <span style="font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:600;
                            @if(in_array($row['status'], ['APPR','INPRG','PTWCL','PTWR','WPTW']))
                                color:#DC2626; background:#FEF2F2; padding:1px 6px; border-radius:3px; border:1px solid #FECACA;
                            @else
                                color:#059669; background:#ECFDF5; padding:1px 6px; border-radius:3px; border:1px solid #A7F3D0;
                            @endif
                        ">{{ $row['status'] }}</span>
                    </td>
                    <td style="font-size:12px; font-weight:500;">{{ $row['plta'] }}</td>
                    <td>
                        @php $statusClass = match($row['status_operasi']) { 'Normal' => 'normal', 'Abnormal' => 'abnormal', default => 'not-ready' }; @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $row['status_operasi'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Confirm/Cancel Actions --}}
    <div style="padding:16px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--border-color); background:var(--bg-surface-2);">
        <a href="{{ route('upload.index') }}" class="btn btn-danger" id="btn-cancel-confirm">
            ✕ Batalkan Upload
        </a>
        <button type="button" class="btn btn-success" id="btn-confirm-upload" onclick="confirmUpload()">
            ✓ Konfirmasi & Upload Data
        </button>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
    // File input display
    const fileInput   = document.getElementById('excel-file-input');
    const fileInfo    = document.getElementById('file-selected-info');
    const fileNameEl  = document.getElementById('file-name-display');
    const fileSizeEl  = document.getElementById('file-size-display');
    const dropzone    = document.getElementById('dropzone');
    const dropzoneIcon = document.getElementById('dropzone-icon');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                fileNameEl.textContent  = file.name;
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                fileSizeEl.textContent  = sizeMB + ' MB · ' + file.type;
                fileInfo.style.display  = 'flex';
                dropzoneIcon.textContent = '✓';
                dropzone.style.borderColor = '#10B981';
                dropzone.style.background  = '#ECFDF5';
            }
        });
    }

    // Drag & drop visual
    if (dropzone) {
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0 && fileInput) {
                // Trigger change event via DataTransfer
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                fileInput.files = dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    }

    // Confirm upload (UI only — tahap prototype)
    function confirmUpload() {
        const btn = document.getElementById('btn-confirm-upload');
        btn.textContent = '⏳ Memproses...';
        btn.disabled    = true;
        btn.style.opacity = '0.7';

        setTimeout(function() {
            btn.textContent  = '✓ Upload Berhasil (Simulasi)';
            btn.style.background = '#059669';
            btn.style.borderColor = '#047857';
            setTimeout(function() {
                window.location.href = '{{ route("upload.index") }}';
            }, 2000);
        }, 1500);
    }
</script>
@endsection
