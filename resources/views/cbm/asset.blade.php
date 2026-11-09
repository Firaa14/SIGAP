{{-- resources/views/cbm/asset.blade.php --}}
@extends('layouts.app')

@section('title', 'Insert Asset CBM')

@section('page-title', 'Insert Asset CBM')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Insert Asset CBM</li>
@endsection

@section('content')
<style>
    .cbm-as { --c-card:#10213f; --c-card2:#0c1a33; --c-line:#1e335c; --c-text:#e2e8f0; --c-mute:#8fa3c4;
              --c-teal:#14b8a6; --c-blue:#2563eb; color:var(--c-text); display:grid; gap:20px; }
    .cbm-as .box { background:var(--c-card); border:1px solid var(--c-line); border-radius:12px; }
    .cbm-as .box-h { padding:16px 20px; border-bottom:1px solid var(--c-line); font-weight:600; }
    .cbm-as .box-b { padding:20px; }
    .cbm-as .grid2 { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .cbm-as .span2 { grid-column:1 / -1; }
    @media (max-width:800px){ .cbm-as .grid2 { grid-template-columns:1fr; } }
    .cbm-as .f { display:flex; flex-direction:column; gap:6px; }
    .cbm-as label.l { font-size:13px; color:var(--c-mute); }
    .cbm-as .hint { font-size:12px; color:var(--c-mute); }
    .cbm-as input[type=text], .cbm-as select { width:100%; background:var(--c-card2); color:var(--c-text);
              border:1px solid var(--c-line); border-radius:8px; padding:10px 12px; font-size:14px; }
    .cbm-as input[type=text]:focus, .cbm-as select:focus { outline:2px solid var(--c-teal); outline-offset:0; }
    .cbm-as .chk { display:inline-flex; align-items:center; gap:6px; font-size:13px; margin:0 16px 6px 0; cursor:pointer; }
    .cbm-as .new { background:#102a5c; color:#60a5fa; font-size:11px; padding:2px 8px; border-radius:6px; margin-left:6px; }
    .cbm-as .actions { display:flex; justify-content:flex-end; gap:10px; margin-top:18px; }
    .cbm-as .btn { padding:9px 16px; border-radius:8px; border:1px solid transparent; font-weight:600; cursor:pointer; font-size:14px; text-decoration:none; display:inline-block; }
    .cbm-as .btn-primary { background:var(--c-blue); color:#fff; }
    .cbm-as .btn-cancel { background:#fff; color:#dc2626; border-color:#fecaca; }
    .cbm-as .alert-ok { background:#dcfce7; color:#166534; padding:12px 16px; border-radius:10px; }
    .cbm-as .alert-bad { background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:10px; }
    .cbm-as .alert-warn { background:#fff7e0; color:#7c4a03; padding:12px 16px; border-radius:10px; border-left:4px solid #f59e0b; }
</style>

<div class="cbm-as">

    @if (session('status'))
        <div class="alert-ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert-bad">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    {{-- ===== Tambah asset baru ===== --}}
    <div class="box">
        <div class="box-h">+ Tambah asset baru</div>
        <div class="box-b">
            <form method="POST" action="{{ route('cbm.asset.store') }}" id="addForm">
                @csrf
                <div class="grid2">
                    <div class="f">
                        <label class="l" for="plta_id">PLTA</label>
                        <select name="plta_id" id="plta_id" required>
                            <option value="">Pilih PLTA</option>
                            @foreach ($pltas as $p)
                                <option value="{{ $p->id }}" @selected((string) old('plta_id') === (string) $p->id)>{{ $p->nama_plta }} ({{ $p->kode_prefix }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="f">
                        <label class="l" for="unit">Unit</label>
                        <input type="text" name="unit" id="unit" list="unitList" value="{{ old('unit') }}"
                               placeholder="Contoh: PLTA SENGGURUH UNIT 1" autocomplete="off" required>
                        <datalist id="unitList"></datalist>
                        <span class="hint">Pilih dari daftar unit PLTA tersebut atau ketik unit baru.</span>
                    </div>

                    <div class="f">
                        <label class="l" for="system">System</label>
                        <input type="text" name="system" id="system" value="{{ old('system') }}"
                               placeholder="Contoh: TA BSGR UNIT #1 TURBINE SYSTEM" required>
                    </div>

                    <div class="f">
                        <label class="l" for="kks">KKS</label>
                        <input type="text" name="kks" id="kks" value="{{ old('kks') }}" placeholder="Contoh: BSGRTA001MEA10HB001">
                    </div>

                    <div class="f span2">
                        <label class="l" for="equipment">Equipment</label>
                        <input type="text" name="equipment" id="equipment" value="{{ old('equipment') }}"
                               placeholder="Contoh: TA-01-RUNNER TURBINE UNIT 1" required>
                    </div>

                    <div class="f">
                        <label class="l" for="assetnum">ASSETNUM</label>
                        <input type="text" name="assetnum" id="assetnum" value="{{ old('assetnum') }}" placeholder="Terisi otomatis">
                        <span class="hint">Terisi otomatis dari PLTA, unit, dan nomor urut. Bisa diubah.</span>
                    </div>

                    <div class="f">
                        <label class="l" for="cbm_alias">Nama di file Excel <span class="new">baru</span></label>
                        <input type="text" name="cbm_alias" id="cbm_alias" value="{{ old('cbm_alias') }}" placeholder="Contoh: Turbin #1">
                        <span class="hint">Opsional. Pisahkan dengan koma bila lebih dari satu.</span>
                    </div>

                    <div class="f span2">
                        <label class="l">Parameter yang berlaku <span class="new">baru</span></label>
                        <div>
                            @foreach ($parameters as $key => $label)
                                <label class="chk">
                                    <input type="checkbox" name="parameters[]" value="{{ $key }}"
                                           @checked(in_array($key, old('parameters', []), true))>{{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <span class="hint">Parameter yang tidak dicentang tampil "-" di tabel PLTA, bukan "Not Measured".</span>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('dashboard') }}" class="btn btn-cancel">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan asset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const unitsByPlta = @json($unitsByPlta);
    const nextUrl = @json(route('cbm.asset.next'));

    // ---- Form tambah asset ----
    const plta = document.getElementById('plta_id');
    const unit = document.getElementById('unit');
    const unitList = document.getElementById('unitList');
    const assetnum = document.getElementById('assetnum');
    let assetnumTouched = assetnum.value !== '';
    assetnum.addEventListener('input', () => { assetnumTouched = true; });

    const fillUnits = () => {
        unitList.innerHTML = '';
        (unitsByPlta[plta.value] || []).forEach(u => {
            const o = document.createElement('option');
            o.value = u;
            unitList.appendChild(o);
        });
    };

    const suggest = async () => {
        if (assetnumTouched || !plta.value || !unit.value.trim()) { return; }
        try {
            const url = nextUrl + '?plta_id=' + encodeURIComponent(plta.value) + '&unit=' + encodeURIComponent(unit.value.trim());
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) { return; }
            const data = await res.json();
            assetnum.value = data.assetnum || '';
        } catch (e) { /* abaikan, ASSETNUM bisa diisi manual */ }
    };

    plta.addEventListener('change', () => { fillUnits(); suggest(); });
    unit.addEventListener('change', suggest);
    unit.addEventListener('blur', suggest);
    fillUnits();
})();
</script>
@endsection