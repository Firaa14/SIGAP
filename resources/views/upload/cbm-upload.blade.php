{{-- resources/views/upload/cbm-upload.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
    .cbm-up { --c-card:#10213f; --c-card2:#0c1a33; --c-line:#1e335c; --c-text:#e2e8f0; --c-mute:#8fa3c4;
              --c-teal:#14b8a6; --c-blue:#2563eb; color:var(--c-text); display:grid; gap:20px; }
    .cbm-up .box { background:var(--c-card); border:1px solid var(--c-line); border-radius:12px; }
    .cbm-up .box-h { padding:16px 20px; border-bottom:1px solid var(--c-line); font-weight:600; display:flex; gap:8px; align-items:center; }
    .cbm-up .box-b { padding:20px; }
    .cbm-up .info { background:#102a5c33; border:1px solid var(--c-line); border-left:4px solid #3b82f6; border-radius:10px; padding:16px 20px; }
    .cbm-up .info h3 { color:#60a5fa; font-size:15px; font-weight:600; margin:0 0 10px; }
    .cbm-up .info li { list-style:none; margin:6px 0; color:#bfd3f5; font-size:14px; }
    .cbm-up .info li::before { content:"→"; color:#60a5fa; margin-right:10px; }
    .cbm-up .warn { background:#fff7e0; border-left:4px solid #f59e0b; border-radius:10px; padding:14px 20px; color:#7c4a03; font-size:14px; }
    .cbm-up .warn b { color:#92400e; }
    .cbm-up .grid2 { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:20px; }
    @media (max-width:900px){ .cbm-up .grid2 { grid-template-columns:1fr; } }
    .cbm-up .tabs { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
    .cbm-up .tab { padding:8px 14px; border-radius:999px; border:1px solid var(--c-line); background:transparent;
                   color:var(--c-mute); cursor:pointer; font-size:13px; font-weight:600; }
    .cbm-up .tab.active { background:var(--c-teal); border-color:var(--c-teal); color:#04201d; }
    .cbm-up .drop { border:2px dashed #cbd5e1; border-radius:12px; padding:36px 16px; text-align:center; cursor:pointer; transition:.15s; }
    .cbm-up .drop.over { background:#14b8a622; border-color:var(--c-teal); }
    .cbm-up .drop .ico { font-size:40px; color:#94a3b8; }
    .cbm-up .drop .t { font-weight:600; margin-top:6px; }
    .cbm-up .drop .s { color:var(--c-mute); font-size:13px; margin-top:4px; }
    .cbm-up .detect { color:var(--c-teal); font-size:13px; margin-top:10px; min-height:18px; }
    .cbm-up .btn { padding:9px 16px; border-radius:8px; border:1px solid transparent; font-weight:600; cursor:pointer; font-size:14px; text-decoration:none; display:inline-block; }
    .cbm-up .btn-pick { background:#fff; color:#0f172a; margin-top:12px; }
    .cbm-up .btn-cancel { background:#fff; color:#dc2626; border-color:#fecaca; }
    .cbm-up .btn-primary { background:var(--c-blue); color:#fff; }
    .cbm-up .btn-ok { background:#16a34a; color:#fff; }
    .cbm-up .btn-ghost { border-color:var(--c-teal); color:var(--c-teal); background:transparent; }
    .cbm-up .btn[disabled] { opacity:.45; cursor:not-allowed; }
    .cbm-up .actions { display:flex; justify-content:flex-end; gap:10px; margin-top:16px; flex-wrap:wrap; }
    .cbm-up .rule { background:#fff; color:#334155; border-radius:10px; padding:14px 16px; margin:8px 0 16px; font-size:14px; }
    .cbm-up .rule-t { font-size:13px; font-weight:700; margin:12px 0 4px; display:flex; align-items:center; gap:8px; }
    .cbm-up .dot { width:9px; height:9px; border-radius:50%; display:inline-block; }
    .cbm-up .badge { display:inline-block; padding:3px 9px; border-radius:6px; color:#fff; font-size:12px; font-weight:700; margin:3px 4px 3px 0; }
    .cbm-up .code-row { display:flex; gap:10px; align-items:center; margin:6px 0; }
    .cbm-up table { width:100%; border-collapse:collapse; font-size:14px; }
    .cbm-up th { text-align:left; padding:10px 16px; background:#f8fafc; color:#64748b; font-size:12px; letter-spacing:.04em; }
    .cbm-up td { padding:12px 16px; border-bottom:1px solid var(--c-line); }
    .cbm-up td.mono { font-family:ui-monospace,monospace; color:#60a5fa; }
    .cbm-up .chips { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
    .cbm-up .chip { padding:8px 14px; border-radius:10px; font-size:13px; font-weight:600; background:var(--c-card2); border:1px solid var(--c-line); }
    .cbm-up .chip.ok { color:#4ade80; } .cbm-up .chip.bad { color:#f87171; }
    .cbm-up .errtbl th { background:#7f1d1d33; color:#fca5a5; }
    .cbm-up .alert-ok { background:#dcfce7; color:#166534; padding:12px 16px; border-radius:10px; }
    .cbm-up .alert-bad { background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:10px; }
    .cbm-up .scroll { max-height:380px; overflow:auto; }
    .cbm-up .hide { display:none; }
    .cbm-up .opt { color:var(--c-mute); font-size:12px; }
</style>

<div class="cbm-up">

    @if (session('status'))
        <div class="alert-ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert-bad">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <div class="info">
        <h3>ⓘ Instruksi Sebelum Upload Data CBM</h3>
        <ul>
            <li>Upload dilakukan <b>satu parameter per file</b>: Thermograph, Vibrasi, Oil Analysis, MCA, PD, atau DGA.</li>
            <li>Pilih parameter yang sesuai dengan file. Parameter otomatis dipilih dari nama file, tetapi tetap periksa kembali.</li>
            <li>Gunakan file resume bulanan apa adanya. Yang dibaca: <b>PLTA</b>, <b>Equipment</b>, <b>No. Equip.</b>, dan hasil akhir (<b>Status Terakhir</b> / Status Akhir / Resume).</li>
            <li><b>ASSETNUM tidak perlu ada di file</b>. Sistem mencarinya otomatis dari master equipment. Tanggal uji otomatis memakai tanggal upload.</li>
            <li>Baris yang belum punya hasil uji (status kosong) dilewati dan tidak dihitung sebagai error.</li>
        </ul>
    </div>

    <div class="warn">
        <b>Perhatian</b><br>
        Data CBM disimpan sebagai <b>riwayat</b>, nilai lama tidak ditimpa. Tabel CBM menampilkan hasil uji terbaru per
        parameter. Parameter yang belum pernah diupload tampil <b>SOON</b>. Setelah diupload, equipment yang tidak punya
        datanya tampil <b>Not Measured</b>.
    </div>

    <div class="grid2">
        {{-- KIRI: upload --}}
        <div class="box">
            <div class="box-h">⬆ Pilih Parameter &amp; File Excel</div>
            <div class="box-b">
                <form id="cbmForm" method="POST" action="{{ route('cbm.upload.preview') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="parameter" id="parameter" value="{{ $selected }}">

                    <div class="tabs" id="tabs">
                        @foreach ($parameters as $key => $label)
                            <button type="button" class="tab {{ $selected === $key ? 'active' : '' }}" data-key="{{ $key }}">{{ $label }}</button>
                        @endforeach
                    </div>

                    <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" class="hide" required>
                    <div class="drop" id="drop">
                        <div class="ico">⬆</div>
                        <div class="t" id="dropTitle">Klik atau seret file ke sini</div>
                        <div class="s" id="dropSub">Format yang diterima: .xlsx, .xls, .csv</div>
                        <button type="button" class="btn btn-pick" id="pick">Pilih File</button>
                    </div>
                    <div class="detect" id="detect"></div>

                    <div class="actions">
                        <a id="tplLink" class="btn btn-ghost" href="#">⬇ Unduh Template</a>
                        <button type="button" class="btn btn-cancel" id="reset">✕ Batalkan</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>⬆ Tampilkan Preview</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- KANAN: aturan status per parameter --}}
        <div class="box">
            <div class="box-h">⚙ Kode Status Parameter</div>
            <div class="box-b">
                @foreach ($legend as $key => $codes)
                    <div class="rule-panel {{ $selected === $key ? '' : 'hide' }}" data-panel="{{ $key }}">
                        <div class="rule-t"><span class="dot" style="background:#14b8a6"></span> {{ $parameters[$key] }}</div>
                        <div class="rule">
                            @foreach ($codes as $code => $meta)
                                <div class="code-row">
                                    <span class="badge" style="background:{{ $meta['color'] }}">{{ $code }}</span>
                                    <span>{{ $meta['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="rule-t"><span class="dot" style="background:#f59e0b"></span> Aturan Penyimpanan</div>
                <div class="rule">
                    Equipment + parameter yang diupload pada <b>hari yang sama</b> akan <b>diperbarui</b>. Upload pada hari
                    berbeda ditambahkan sebagai <b>riwayat baru</b>. Tabel CBM mengambil data paling baru.
                    Jika satu equipment punya beberapa baris/item uji, yang disimpan adalah <b>status terparah</b>.
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom yang diperlukan --}}
    <div class="box">
        <div class="box-h">≡ Kolom yang Diperlukan</div>
        <table>
            <thead><tr><th>NAMA KOLOM</th><th>DESKRIPSI</th><th style="text-align:right">WAJIB</th></tr></thead>
            <tbody>
                <tr><td class="mono">PLTA</td><td>Kode PLTA (contoh: SGR, STM, TLG)</td><td style="text-align:right;color:#4ade80">✓</td></tr>
                <tr><td class="mono">EQUIPMENT</td><td>Nama equipment sesuai file resume (contoh: Turbin #1)</td><td style="text-align:right;color:#4ade80">✓</td></tr>
                <tr><td class="mono">STATUS TERAKHIR</td><td>Hasil akhir pengujian (kode sesuai parameter). Nama kolom bisa juga Status Akhir atau Resume</td><td style="text-align:right;color:#4ade80">✓</td></tr>
                <tr><td class="mono">NO. EQUIP.</td><td>Kode equipment (contoh: H101). Membantu mencocokkan ke master equipment</td><td style="text-align:right" class="opt">opsional</td></tr>
                <tr><td class="mono">ASSETNUM</td><td>Jika ada, dipakai langsung. Jika tidak ada, dicari otomatis dari master equipment</td><td style="text-align:right" class="opt">opsional</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Hasil preview --}}
    @if ($preview)
        @php
            $byStatus = collect($preview['valid'])->countBy('status_code');
            $meta = $legend[$preview['parameter']] ?? [];
        @endphp
        <div class="box">
            <div class="box-h">👁 Preview: {{ $preview['filename'] }} ({{ $parameters[$preview['parameter']] }})</div>
            <div class="box-b">
                <div class="chips">
                    <span class="chip ok">{{ count($preview['valid']) }} baris valid</span>
                    <span class="chip bad">{{ count($preview['errors']) }} baris gagal</span>
                    @foreach ($byStatus as $code => $n)
                        <span class="chip">
                            <span class="badge" style="background:{{ $meta[$code]['color'] ?? '#64748b' }}">{{ $code }}</span> {{ $n }}
                        </span>
                    @endforeach
                </div>

                @if (count($preview['errors']))
                    <div class="scroll" style="margin-bottom:16px">
                        <table class="errtbl">
                            <thead><tr><th>BARIS</th><th>PLTA</th><th>EQUIPMENT</th><th>ASSETNUM</th><th>ALASAN</th></tr></thead>
                            <tbody>
                                @foreach ($preview['errors'] as $err)
                                    <tr>
                                        <td>{{ $err['row'] }}</td>
                                        <td>{{ $err['plta'] ?? '—' }}</td>
                                        <td>{{ $err['equipment'] ?? '—' }}</td>
                                        <td>{{ $err['assetnum'] ?? '—' }}</td>
                                        <td>{{ $err['reason'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if (count($preview['valid']))
                    <div class="scroll">
                        <table>
                            <thead><tr><th>ASSETNUM</th><th>PLTA</th><th>EQUIPMENT (DI FILE)</th><th>HASIL AKHIR</th></tr></thead>
                            <tbody>
                                @foreach (array_slice($preview['valid'], 0, 100) as $row)
                                    <tr>
                                        <td class="mono">{{ $row['assetnum'] }}</td>
                                        <td>{{ ($row['plta'] ?? '') !== '' ? $row['plta'] : '—' }}</td>
                                        <td>{{ ($row['equipment'] ?? '') !== '' ? $row['equipment'] : '—' }}</td>
                                        <td>
                                            <span class="badge" style="background:{{ $meta[$row['status_code']]['color'] ?? '#64748b' }}">{{ $row['status_code'] }}</span>
                                            {{ $meta[$row['status_code']]['label'] ?? '' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (count($preview['valid']) > 100)
                        <p style="color:var(--c-mute);font-size:13px;margin-top:8px">Menampilkan 100 dari {{ count($preview['valid']) }} baris.</p>
                    @endif

                    <div class="actions">
                        <form method="POST" action="{{ route('cbm.upload.cancel') }}">@csrf
                            <button class="btn btn-cancel" type="submit">✕ Batalkan</button>
                        </form>
                        <form method="POST" action="{{ route('cbm.upload.confirm') }}">@csrf
                            <button class="btn btn-ok" type="submit">✓ Simpan {{ count($preview['valid']) }} Baris Valid</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
(() => {
    const tplBase = @json(url('/cbm/upload/template'));
    const param = document.getElementById('parameter');
    const file = document.getElementById('file');
    const drop = document.getElementById('drop');
    const submitBtn = document.getElementById('submitBtn');
    const tplLink = document.getElementById('tplLink');
    const detect = document.getElementById('detect');
    const tabs = [...document.querySelectorAll('#tabs .tab')];

    const selectParam = key => {
        param.value = key;
        tabs.forEach(t => t.classList.toggle('active', t.dataset.key === key));
        document.querySelectorAll('.rule-panel').forEach(p => p.classList.toggle('hide', p.dataset.panel !== key));
        tplLink.href = tplBase + '/' + key;
    };
    tabs.forEach(t => t.addEventListener('click', () => { detect.textContent = ''; selectParam(t.dataset.key); }));
    selectParam(param.value);

    // Tebak parameter dari nama file (key, label, atau alias).
    const aliases = { irt: 'thermo', infrared: 'thermo', thermography: 'thermo', thermograph: 'thermo',
                      vibration: 'vibra', vibrasi: 'vibra', oil: 'oil', mca: 'mca', mcsa: 'mca',
                      'partial discharge': 'pd', pd: 'pd', dga: 'dga' };
    const guessTab = name => {
        const n = name.toLowerCase();
        const match = needle => tabs.find(t => (t.dataset.key + ' ' + t.textContent).toLowerCase().includes(needle));
        for (const [word, needle] of Object.entries(aliases)) {
            if (new RegExp('(^|[^a-z])' + word + '([^a-z]|$)').test(n)) { const t = match(needle); if (t) return t; }
        }
        return tabs.find(t => n.includes(t.dataset.key.toLowerCase()) || n.includes(t.textContent.trim().toLowerCase()));
    };

    const showFile = () => {
        const f = file.files[0];
        document.getElementById('dropTitle').textContent = f ? f.name : 'Klik atau seret file ke sini';
        document.getElementById('dropSub').textContent = f ? (f.size / 1024).toFixed(1) + ' KB' : 'Format yang diterima: .xlsx, .xls, .csv';
        submitBtn.disabled = !f;
        detect.textContent = '';
        if (f) {
            const t = guessTab(f.name);
            if (t) { selectParam(t.dataset.key); detect.textContent = 'Parameter otomatis dipilih: ' + t.textContent.trim() + '. Ganti tab bila tidak sesuai.'; }
            else { detect.textContent = 'Parameter tidak terdeteksi dari nama file. Pastikan tab yang aktif: ' + (tabs.find(x => x.classList.contains('active'))?.textContent.trim() ?? '-') + '.'; }
        }
    };
    document.getElementById('pick').addEventListener('click', e => { e.stopPropagation(); file.click(); });
    drop.addEventListener('click', () => file.click());
    file.addEventListener('change', showFile);
    ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('over'); }));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('over'); }));
    drop.addEventListener('drop', e => { if (e.dataTransfer.files.length) { file.files = e.dataTransfer.files; showFile(); } });
    document.getElementById('reset').addEventListener('click', () => { file.value = ''; showFile(); });
})();
</script>
@endsection