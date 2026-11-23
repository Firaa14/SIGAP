{{-- resources/views/cbm/asset.blade.php --}}
@extends('layouts.app')

@section('title', 'Insert Asset CBM')

@section('page-title', 'Insert Asset CBM')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Insert Asset CBM</li>
@endsection

@section('content')
@php
    // Mode edit: dikirim controller lewat /cbm/asset?edit=ASSETNUM (tombol "Edit" di tabel PLTA)
    $edit = $edit ?? null;
    $editParams = $edit ? array_values(array_filter(explode(',', (string) ($edit->cbm_parameters ?? '')))) : [];
@endphp
<style>
    /* Warna mengikuti tema aplikasi (dark/light) lewat variabel CSS milik layout. */
    .cbm-as { --c-line:var(--border-color, #e2e8f0); --c-mute:var(--text-muted, #64748b);
              --c-teal:#14b8a6; --c-blue:#2563eb; color:var(--text-primary); display:grid; gap:20px; }
    .cbm-as .box { border-radius:12px; }
    .cbm-as .box-h { padding:16px 20px; border-bottom:1px solid var(--c-line); font-weight:600; }
    .cbm-as .box-b { padding:20px; }
    .cbm-as .grid2 { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .cbm-as .span2 { grid-column:1 / -1; }
    @media (max-width:800px){ .cbm-as .grid2 { grid-template-columns:1fr; } }
    .cbm-as .f { display:flex; flex-direction:column; gap:6px; }
    .cbm-as label.l { font-size:13px; color:var(--c-mute); }
    .cbm-as .hint { font-size:12px; color:var(--c-mute); }
    .cbm-as input[type=text], .cbm-as select { width:100%; background:var(--bg-surface-2, #fff); color:var(--text-primary);
              border:1px solid var(--c-line); border-radius:8px; padding:10px 12px; font-size:14px; }
    .cbm-as input[type=text]:focus, .cbm-as select:focus { outline:2px solid var(--c-teal); outline-offset:0; }
    .cbm-as input[readonly] { opacity:.7; cursor:not-allowed; }
    .cbm-as .chk { display:inline-flex; align-items:center; gap:6px; font-size:13px; margin:0 16px 6px 0; cursor:pointer; }
    .cbm-as .new { background:var(--color-blue-light, rgba(37,99,235,.12)); color:var(--color-accent, #2563eb); font-size:11px; padding:2px 8px; border-radius:6px; margin-left:6px; }
    .cbm-as .actions { display:flex; justify-content:flex-end; gap:10px; margin-top:18px; }
    .cbm-as .btn { padding:9px 16px; border-radius:8px; border:1px solid transparent; font-weight:600; cursor:pointer; font-size:14px; text-decoration:none; display:inline-block; }
    .cbm-as .btn-primary { background:var(--c-blue); color:#fff; }
    .cbm-as .btn-cancel { background:#fff; color:#dc2626; border-color:#fecaca; }
    .cbm-as .alert-ok { background:#dcfce7; color:#166534; padding:12px 16px; border-radius:10px; }
    .cbm-as .alert-bad { background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:10px; }
    .cbm-as .alert-warn { background:#fff7e0; color:#7c4a03; padding:12px 16px; border-radius:10px; border-left:4px solid #f59e0b; }
    .cbm-as .alert-edit { background:var(--color-blue-light, rgba(37,99,235,.12)); border-left:4px solid #3b82f6; padding:12px 16px; border-radius:10px; font-size:14px; }
</style>

<div class="cbm-as">

    @if (session('status'))
        <div class="alert-ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert-bad">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    @if (request()->query('edit') && ! $edit)
        <div class="alert-warn">Alat dengan ASSETNUM <strong>{{ request()->query('edit') }}</strong> tidak ditemukan.</div>
    @endif

    {{-- ===== Tambah asset baru / Edit asset ===== --}}
    <div class="box card">
        @if ($edit)
            <div class="box-h">✎ Edit asset {{ $edit->assetnum }}</div>
        @else
            <div class="box-h" data-cbm-i18n="as_add_title">+ Tambah asset baru</div>
        @endif
        <div class="box-b">
            @if ($edit)
                <div class="alert-edit" style="margin-bottom:16px">
                    Sedang mengedit <strong>{{ $edit->assetnum }}</strong>. ASSETNUM tidak berubah dan riwayat CBM tetap menempel.
                    Nama equipment yang baru ditambahkan ke alias, alias lama tetap.
                </div>
            @endif

            <form method="POST" action="{{ route('cbm.asset.store') }}" id="addForm">
                @csrf
                @if ($edit)
                    <input type="hidden" name="edit_assetnum" value="{{ $edit->assetnum }}">
                @endif

                <div class="grid2">
                    <div class="f">
                        <label class="l" for="plta_id">PLTA</label>
                        <select name="plta_id" id="plta_id" required>
                            <option value="" data-cbm-i18n="as_select_plta">Pilih PLTA</option>
                            @foreach ($pltas as $p)
                                <option value="{{ $p->id }}" @selected((string) old('plta_id', $edit->plta_id ?? '') === (string) $p->id)>{{ $p->nama_plta }} ({{ $p->kode_prefix }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="f">
                        <label class="l" for="unit">Unit</label>
                        <input type="text" name="unit" id="unit" list="unitList" value="{{ old('unit', $edit->unit ?? '') }}"
                               placeholder="Contoh: PLTA SENGGURUH UNIT 1" data-cbm-i18n-placeholder="as_unit_ph" autocomplete="off" required>
                        <datalist id="unitList"></datalist>
                        <span class="hint" data-cbm-i18n="as_unit_hint">Pilih dari daftar unit PLTA tersebut atau ketik unit baru.</span>
                    </div>

                    <div class="f">
                        <label class="l" for="system">System</label>
                        <input type="text" name="system" id="system" value="{{ old('system', $edit->system ?? '') }}"
                               placeholder="Contoh: TA BSGR UNIT #1 TURBINE SYSTEM" data-cbm-i18n-placeholder="as_system_ph" required>
                    </div>

                    <div class="f">
                        <label class="l" for="kks">KKS</label>
                        <input type="text" name="kks" id="kks" value="{{ old('kks', $edit->kks ?? '') }}" placeholder="Contoh: BSGRTA001MEA10HB001" data-cbm-i18n-placeholder="as_kks_ph">
                    </div>

                    <div class="f span2">
                        <label class="l" for="equipment">Equipment</label>
                        <input type="text" name="equipment" id="equipment" value="{{ old('equipment', $edit->equipment ?? '') }}"
                               placeholder="Contoh: TA-01-RUNNER TURBINE UNIT 1" data-cbm-i18n-placeholder="as_equipment_ph" required>
                    </div>

                    <div class="f">
                        <label class="l" for="assetnum">ASSETNUM</label>
                        <input type="text" name="assetnum" id="assetnum" value="{{ old('assetnum', $edit->assetnum ?? '') }}"
                               placeholder="Contoh: BSGR010001" autocomplete="off" required @if ($edit) readonly @endif>
                        @if ($edit)
                            <span class="hint">ASSETNUM tidak bisa diubah pada mode edit.</span>
                        @else
                            <span class="hint">Isi manual sesuai nomor resmi.</span>
                        @endif
                    </div>

                    <div class="f span2">
                        <label class="l" data-cbm-i18n="as_params_label_new">Parameter yang berlaku <span class="new">baru</span></label>
                        <div>
                            @foreach ($parameters as $key => $label)
                                <label class="chk">
                                    <input type="checkbox" name="parameters[]" value="{{ $key }}"
                                           @checked(in_array($key, old('parameters', $editParams), true))>{{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <span class="hint" data-cbm-i18n="as_params_hint">Parameter yang tidak dicentang tampil "-" di tabel PLTA, bukan "Not Measured".</span>
                    </div>
                </div>

                <div class="actions">
                    @if ($edit)
                        <a href="{{ route('cbm.asset.create') }}" class="btn btn-cancel">Batal edit</a>
                        <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-cancel" data-cbm-i18n="as_cancel">Batal</a>
                        <button type="submit" class="btn btn-primary" data-cbm-i18n="as_save">Simpan asset</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/cbm-bahasa.js') }}"></script>
<script>
(() => {
    const unitsByPlta = @json($unitsByPlta);

    const plta = document.getElementById('plta_id');
    const unitList = document.getElementById('unitList');

    // Daftar saran unit mengikuti PLTA yang dipilih.
    const fillUnits = () => {
        unitList.innerHTML = '';
        (unitsByPlta[plta.value] || []).forEach(u => {
            const o = document.createElement('option');
            o.value = u;
            unitList.appendChild(o);
        });
    };

    plta.addEventListener('change', fillUnits);
    fillUnits();
})();
</script>
@endsection