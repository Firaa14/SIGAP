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
    .cbm-as .chk { display:inline-flex; align-items:center; gap:6px; font-size:13px; margin:0 16px 6px 0; cursor:pointer; }
    .cbm-as .new { background:var(--color-blue-light, rgba(37,99,235,.12)); color:var(--color-accent, #2563eb); font-size:11px; padding:2px 8px; border-radius:6px; margin-left:6px; }
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

    {{-- ===== 1. Tambah asset baru ===== --}}
    <div class="box card">
        <div class="box-h" data-cbm-i18n="as_add_title">+ Tambah asset baru</div>
        <div class="box-b">
            <form method="POST" action="{{ route('cbm.asset.store') }}" id="addForm">
                @csrf
                <div class="grid2">
                    <div class="f">
                        <label class="l" for="plta_id">PLTA</label>
                        <select name="plta_id" id="plta_id" required>
                            <option value="" data-cbm-i18n="as_select_plta">Pilih PLTA</option>
                            @foreach ($pltas as $p)
                                <option value="{{ $p->id }}" @selected((string) old('plta_id') === (string) $p->id)>{{ $p->nama_plta }} ({{ $p->kode_prefix }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="f">
                        <label class="l" for="unit">Unit</label>
                        <input type="text" name="unit" id="unit" list="unitList" value="{{ old('unit') }}"
                               placeholder="Contoh: PLTA SENGGURUH UNIT 1" data-cbm-i18n-placeholder="as_unit_ph" autocomplete="off" required>
                        <datalist id="unitList"></datalist>
                        <span class="hint" data-cbm-i18n="as_unit_hint">Pilih dari daftar unit PLTA tersebut atau ketik unit baru.</span>
                    </div>

                    <div class="f">
                        <label class="l" for="system">System</label>
                        <input type="text" name="system" id="system" value="{{ old('system') }}"
                               placeholder="Contoh: TA BSGR UNIT #1 TURBINE SYSTEM" data-cbm-i18n-placeholder="as_system_ph" required>
                    </div>

                    <div class="f">
                        <label class="l" for="kks">KKS</label>
                        <input type="text" name="kks" id="kks" value="{{ old('kks') }}" placeholder="Contoh: BSGRTA001MEA10HB001" data-cbm-i18n-placeholder="as_kks_ph">
                    </div>

                    <div class="f span2">
                        <label class="l" for="equipment">Equipment</label>
                        <input type="text" name="equipment" id="equipment" value="{{ old('equipment') }}"
                               placeholder="Contoh: TA-01-RUNNER TURBINE UNIT 1" data-cbm-i18n-placeholder="as_equipment_ph" required>
                    </div>

                    <div class="f">
                        <label class="l" for="assetnum">ASSETNUM</label>
                        <input type="text" name="assetnum" id="assetnum" value="{{ old('assetnum') }}" placeholder="Terisi otomatis" data-cbm-i18n-placeholder="as_assetnum_ph">
                        <span class="hint" data-cbm-i18n="as_assetnum_hint">Terisi otomatis dari PLTA, unit, dan nomor urut. Bisa diubah.</span>
                    </div>

                    <div class="f">
                        <label class="l" for="cbm_alias" data-cbm-i18n="as_alias_label_new">Nama di file Excel <span class="new">baru</span></label>
                        <input type="text" name="cbm_alias" id="cbm_alias" value="{{ old('cbm_alias') }}" placeholder="Contoh: Turbin #1" data-cbm-i18n-placeholder="as_alias_ph">
                        <span class="hint" data-cbm-i18n="as_alias_hint">Opsional. Pisahkan dengan koma bila lebih dari satu.</span>
                    </div>

                    <div class="f span2">
                        <label class="l" data-cbm-i18n="as_params_label_new">Parameter yang berlaku <span class="new">baru</span></label>
                        <div>
                            @foreach ($parameters as $key => $label)
                                <label class="chk">
                                    <input type="checkbox" name="parameters[]" value="{{ $key }}"
                                           @checked(in_array($key, old('parameters', []), true))>{{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <span class="hint" data-cbm-i18n="as_params_hint">Parameter yang tidak dicentang tampil "-" di tabel PLTA, bukan "Not Measured".</span>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('dashboard') }}" class="btn btn-cancel" data-cbm-i18n="as_cancel">Batal</a>
                    <button type="submit" class="btn btn-primary" data-cbm-i18n="as_save">Simpan asset</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== 2. Atur alias & parameter equipment yang sudah ada ===== --}}
    <div class="box card">
        <div class="box-h" data-cbm-i18n="as_edit_title">Atur nama di file Excel dan parameter equipment yang sudah ada</div>
        <div class="box-b">
            <form method="POST" action="{{ route('cbm.asset.alias') }}" id="aliasForm">
                @csrf
                <div class="grid2">
                    <div class="f">
                        <label class="l" for="plta2">PLTA</label>
                        <select id="plta2">
                            <option value="" data-cbm-i18n="as_select_plta">Pilih PLTA</option>
                            @foreach ($pltas as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_plta }} ({{ $p->kode_prefix }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="f">
                        <label class="l" for="assetnum2">Equipment</label>
                        <select name="assetnum" id="assetnum2" required>
                            <option value="" data-cbm-i18n="as_select_plta_first">Pilih PLTA lebih dulu</option>
                        </select>
                    </div>

                    <div class="f span2">
                        <label class="l" for="alias2" data-cbm-i18n="as_alias_label">Nama di file Excel</label>
                        <input type="text" name="cbm_alias" id="alias2" placeholder="Contoh: Turbin #1, Turbine Vib X #1" data-cbm-i18n-placeholder="as_alias2_ph">
                        <span class="hint" data-cbm-i18n="as_alias2_hint">Pisahkan dengan koma. Baris di Excel dengan nama ini otomatis masuk ke equipment di atas saat upload.</span>
                    </div>

                    <div class="f span2">
                        <label class="l" data-cbm-i18n="as_params_label">Parameter yang berlaku</label>
                        <div id="params2">
                            @foreach ($parameters as $key => $label)
                                <label class="chk"><input type="checkbox" name="parameters[]" value="{{ $key }}">{{ $label }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary" data-cbm-i18n="as_save_settings">Simpan pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/cbm-bahasa.js') }}"></script>
<script>
(() => {
    const unitsByPlta = @json($unitsByPlta);
    const nextUrl = @json(route('cbm.asset.next'));
    const listUrl = @json(route('cbm.asset.equipments'));

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

    // ---- Atur alias equipment yang sudah ada ----
    const plta2 = document.getElementById('plta2');
    const eqSelect = document.getElementById('assetnum2');
    const alias2 = document.getElementById('alias2');
    const boxes = [...document.querySelectorAll('#params2 input[type=checkbox]')];
    let items = [];

    // Opsi kosong pada daftar equipment (teks mengikuti bahasa aktif).
    let eqPlaceholderKey = 'as_select_plta_first';
    const setEqPlaceholder = key => {
        eqPlaceholderKey = key;
        eqSelect.innerHTML = '';
        const o = document.createElement('option');
        o.value = '';
        o.textContent = CBM_T(key);
        eqSelect.appendChild(o);
    };
    document.addEventListener('cbm:langapplied', () => {
        const first = eqSelect.options[0];
        if (first && first.value === '') { first.textContent = CBM_T(eqPlaceholderKey); }
    });

    const showItem = () => {
        const item = items.find(i => i.assetnum === eqSelect.value);
        alias2.value = item ? item.alias : '';
        boxes.forEach(b => { b.checked = !!item && item.parameters.includes(b.value); });
    };

    plta2.addEventListener('change', async () => {
        setEqPlaceholder('js_loading');
        items = [];
        showItem();
        if (!plta2.value) { setEqPlaceholder('as_select_plta_first'); return; }
        try {
            const res = await fetch(listUrl + '?plta_id=' + encodeURIComponent(plta2.value), { headers: { 'Accept': 'application/json' } });
            items = res.ok ? await res.json() : [];
        } catch (e) { items = []; }
        setEqPlaceholder('js_select_equipment');
        items.forEach(i => {
            const o = document.createElement('option');
            o.value = i.assetnum;
            o.textContent = i.assetnum + ' — ' + i.equipment;
            eqSelect.appendChild(o);
        });
    });
    eqSelect.addEventListener('change', showItem);
})();
</script>
@endsection