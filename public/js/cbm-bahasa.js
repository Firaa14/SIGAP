// Terjemahan ID/EN untuk halaman CBM, mengikuti tombol bahasa di layout.
(function () {
    'use strict';

    var DICT = {
        id: {
            // ---------- Upload CBM ----------
            up_info_title: 'ⓘ Instruksi Sebelum Upload Data CBM',
            up_info_1: 'Upload dilakukan <b>satu parameter per file</b>: Thermograph, Vibrasi, Oil Analysis, MCA, PD, atau DGA.',
            up_info_2: 'Pilih parameter yang sesuai dengan file. Parameter otomatis dipilih dari nama file, tetapi tetap periksa kembali.',
            up_info_3: 'Gunakan file resume bulanan apa adanya. Yang dibaca: <b>PLTA</b>, <b>Equipment</b>, <b>No. Equip.</b>, dan hasil akhir (<b>Status Terakhir</b> / Status Akhir / Resume).',
            up_info_4: '<b>ASSETNUM tidak perlu ada di file</b>. Sistem mencarinya otomatis dari master equipment. Tanggal uji otomatis memakai tanggal upload.',
            up_info_5: 'Baris yang belum punya hasil uji (status kosong) dilewati dan tidak dihitung sebagai error.',
            up_warn_title: 'Perhatian',
            up_warn_text: 'Data CBM disimpan sebagai <b>riwayat</b>, nilai lama tidak ditimpa. Tabel CBM menampilkan hasil uji terbaru per parameter. Parameter yang belum pernah diupload tampil <b>SOON</b>. Setelah diupload, equipment yang tidak punya datanya tampil <b>Not Measured</b>.',
            up_box_pick: '⬆ Pilih Parameter &amp; File Excel',
            up_pick: 'Pilih File',
            up_cancel: '✕ Batalkan',
            up_submit: '⬆ Tampilkan Preview',
            up_box_codes: '⚙ Kode Status Parameter',
            up_rule_title: 'Aturan Penyimpanan',
            up_rule_text: 'Equipment + parameter yang diupload pada <b>hari yang sama</b> akan <b>diperbarui</b>. Upload pada hari berbeda ditambahkan sebagai <b>riwayat baru</b>. Tabel CBM mengambil data paling baru. Jika satu equipment punya beberapa baris/item uji, yang disimpan adalah <b>status terparah</b>.',
            up_box_cols: '≡ Kolom yang Diperlukan',
            up_col_name: 'NAMA KOLOM',
            up_col_desc: 'DESKRIPSI',
            up_col_req: 'WAJIB',
            up_col_plta: 'Kode PLTA (contoh: SGR, STM, TLG)',
            up_col_equipment: 'Nama equipment sesuai file resume (contoh: Turbin #1)',
            up_col_status: 'Hasil akhir pengujian (kode sesuai parameter). Nama kolom bisa juga Status Akhir atau Resume',
            up_col_noequip: 'Kode equipment (contoh: H101). Membantu mencocokkan ke master equipment',
            up_col_assetnum: 'Jika ada, dipakai langsung. Jika tidak ada, dicari otomatis dari master equipment',
            up_optional: 'opsional',
            up_preview: 'Preview',
            up_valid_rows: 'baris valid',
            up_failed_rows: 'baris gagal',
            up_th_row: 'BARIS',
            up_th_reason: 'ALASAN',
            up_th_file_eq: 'EQUIPMENT (DI FILE)',
            up_th_master_eq: 'EQUIPMENT (MASTER)',
            up_th_result: 'HASIL AKHIR',
            up_showing: 'Menampilkan 100 dari {n} baris.',
            up_save: '✓ Simpan {n} Baris Valid',
            js_drop_title: 'Klik atau seret file ke sini',
            js_drop_sub: 'Format yang diterima: .xlsx, .xls, .csv',
            js_auto: 'Parameter terdeteksi otomatis dari nama file: {p}',
            js_none: 'Parameter tidak terdeteksi dari nama file, memakai: {p}',
            js_loading: 'Memuat…',
            js_select_equipment: 'Pilih equipment',

            // ---------- Insert Asset CBM ----------
            as_add_title: '+ Tambah asset baru',
            as_select_plta: 'Pilih PLTA',
            as_select_plta_first: 'Pilih PLTA lebih dulu',
            as_unit_ph: 'Contoh: PLTA SENGGURUH UNIT 1',
            as_unit_hint: 'Pilih dari daftar unit PLTA tersebut atau ketik unit baru.',
            as_system_ph: 'Contoh: TA BSGR UNIT #1 TURBINE SYSTEM',
            as_kks_ph: 'Contoh: BSGRTA001MEA10HB001',
            as_equipment_ph: 'Contoh: TA-01-RUNNER TURBINE UNIT 1',
            as_assetnum_ph: 'Terisi otomatis',
            as_assetnum_hint: 'Terisi otomatis dari PLTA, unit, dan nomor urut. Bisa diubah.',
            as_alias_label_new: 'Nama di file Excel <span class="new">baru</span>',
            as_alias_label: 'Nama di file Excel',
            as_alias_ph: 'Contoh: Turbin #1',
            as_alias_hint: 'Opsional. Pisahkan dengan koma bila lebih dari satu.',
            as_params_label_new: 'Parameter yang berlaku <span class="new">baru</span>',
            as_params_label: 'Parameter yang berlaku',
            as_params_hint: 'Parameter yang tidak dicentang tampil "-" di tabel PLTA, bukan "Not Measured".',
            as_cancel: 'Batal',
            as_save: 'Simpan asset',
            as_edit_title: 'Atur nama di file Excel dan parameter equipment yang sudah ada',
            as_alias2_ph: 'Contoh: Turbin #1, Turbine Vib X #1',
            as_alias2_hint: 'Pisahkan dengan koma. Baris di Excel dengan nama ini otomatis masuk ke equipment di atas saat upload.',
            as_save_settings: 'Simpan pengaturan',

            // ---------- Tabel PLTA (CBM) ----------
            plta_cbm_title: 'Data Pengujian CBM',
            plta_cbm_shown: 'equipment ditampilkan',
            plta_cbm_empty_title: 'Belum ada data equipment',
            plta_cbm_empty_a: 'Data pengujian CBM untuk',
            plta_cbm_empty_b: 'belum tersedia.',

            // ---------- Dashboard (CBM) ----------
            dash_cbm_activity: 'Aktivitas Status Terbaru',
            dash_cbm_all: 'Semua',
            dash_cbm_th_upload: 'Waktu Upload',
            dash_cbm_th_test_date: 'Tanggal Uji',
            dash_cbm_empty_title: 'Belum ada data',
            dash_cbm_empty_param: 'Belum ada data uji untuk parameter ini.',
            dash_cbm_empty_all: 'Upload file Excel CBM untuk melihat aktivitas terbaru.'
        },

        en: {
            // ---------- Upload CBM ----------
            up_info_title: 'ⓘ Instructions Before Uploading CBM Data',
            up_info_1: 'Upload <b>one parameter per file</b>: Thermograph, Vibration, Oil Analysis, MCA, PD, or DGA.',
            up_info_2: 'Choose the parameter that matches the file. The parameter is picked automatically from the file name, but please double-check it.',
            up_info_3: 'Use the monthly resume file as it is. These are read: <b>PLTA</b>, <b>Equipment</b>, <b>No. Equip.</b>, and the final result (<b>Status Terakhir</b> / Final Status / Resume).',
            up_info_4: '<b>ASSETNUM does not need to be in the file</b>. The system looks it up automatically from the equipment master. The test date automatically uses the upload date.',
            up_info_5: 'Rows without a test result yet (empty status) are skipped and are not counted as errors.',
            up_warn_title: 'Attention',
            up_warn_text: 'CBM data is saved as <b>history</b>, old values are not overwritten. The CBM table shows the latest test result per parameter. Parameters that have never been uploaded show <b>SOON</b>. Once uploaded, equipment without data for it shows <b>Not Measured</b>.',
            up_box_pick: '⬆ Choose Parameter &amp; Excel File',
            up_pick: 'Choose File',
            up_cancel: '✕ Cancel',
            up_submit: '⬆ Show Preview',
            up_box_codes: '⚙ Parameter Status Codes',
            up_rule_title: 'Storage Rules',
            up_rule_text: 'Equipment + parameter uploaded on the <b>same day</b> are <b>updated</b>. An upload on a different day is added as a <b>new history entry</b>. The CBM table uses the latest data. If one equipment has several test rows/items, the <b>worst status</b> is saved.',
            up_box_cols: '≡ Required Columns',
            up_col_name: 'COLUMN NAME',
            up_col_desc: 'DESCRIPTION',
            up_col_req: 'REQUIRED',
            up_col_plta: 'PLTA code (e.g. SGR, STM, TLG)',
            up_col_equipment: 'Equipment name as in the resume file (e.g. Turbin #1)',
            up_col_status: 'Final test result (code according to the parameter). The column may also be named Final Status or Resume',
            up_col_noequip: 'Equipment code (e.g. H101). Helps match to the equipment master',
            up_col_assetnum: 'If present, it is used directly. If not, it is looked up automatically from the equipment master',
            up_optional: 'optional',
            up_preview: 'Preview',
            up_valid_rows: 'valid rows',
            up_failed_rows: 'failed rows',
            up_th_row: 'ROW',
            up_th_reason: 'REASON',
            up_th_file_eq: 'EQUIPMENT (IN FILE)',
            up_th_master_eq: 'EQUIPMENT (MASTER)',
            up_th_result: 'FINAL RESULT',
            up_showing: 'Showing 100 of {n} rows.',
            up_save: '✓ Save {n} Valid Rows',
            js_drop_title: 'Click or drag a file here',
            js_drop_sub: 'Accepted formats: .xlsx, .xls, .csv',
            js_auto: 'Parameter detected automatically from the file name: {p}',
            js_none: 'Parameter not detected from the file name, using: {p}',
            js_loading: 'Loading…',
            js_select_equipment: 'Select equipment',

            // ---------- Insert Asset CBM ----------
            as_add_title: '+ Add new asset',
            as_select_plta: 'Select PLTA',
            as_select_plta_first: 'Select a PLTA first',
            as_unit_ph: 'Example: PLTA SENGGURUH UNIT 1',
            as_unit_hint: 'Pick from that PLTA\'s unit list or type a new unit.',
            as_system_ph: 'Example: TA BSGR UNIT #1 TURBINE SYSTEM',
            as_kks_ph: 'Example: BSGRTA001MEA10HB001',
            as_equipment_ph: 'Example: TA-01-RUNNER TURBINE UNIT 1',
            as_assetnum_ph: 'Filled automatically',
            as_assetnum_hint: 'Filled automatically from the PLTA, unit, and sequence number. You can change it.',
            as_alias_label_new: 'Name in Excel file <span class="new">new</span>',
            as_alias_label: 'Name in Excel file',
            as_alias_ph: 'Example: Turbin #1',
            as_alias_hint: 'Optional. Separate with commas if there is more than one.',
            as_params_label_new: 'Applicable parameters <span class="new">new</span>',
            as_params_label: 'Applicable parameters',
            as_params_hint: 'Unticked parameters show "-" in the PLTA table, not "Not Measured".',
            as_cancel: 'Cancel',
            as_save: 'Save asset',
            as_edit_title: 'Manage Excel file names and parameters of existing equipment',
            as_alias2_ph: 'Example: Turbin #1, Turbine Vib X #1',
            as_alias2_hint: 'Separate with commas. Excel rows with this name automatically go to the equipment above when uploading.',
            as_save_settings: 'Save settings',

            // ---------- Tabel PLTA (CBM) ----------
            plta_cbm_title: 'CBM Test Data',
            plta_cbm_shown: 'equipment displayed',
            plta_cbm_empty_title: 'No equipment data yet',
            plta_cbm_empty_a: 'CBM test data for',
            plta_cbm_empty_b: 'is not yet available.',

            // ---------- Dashboard (CBM) ----------
            dash_cbm_activity: 'Latest Status Activity',
            dash_cbm_all: 'All',
            dash_cbm_th_upload: 'Upload Time',
            dash_cbm_th_test_date: 'Test Date',
            dash_cbm_empty_title: 'No data yet',
            dash_cbm_empty_param: 'There is no test data for this parameter yet.',
            dash_cbm_empty_all: 'Upload a CBM Excel file to see the latest activity.'
        }
    };

    // Teks tetap (tanpa atribut) yang ikut diterjemahkan, mis. judul kolom dan modal detail.
    // Hanya elemen "daun" (tanpa anak elemen) di dalam <main> dengan teks persis sama.
    var AUTO = {
        'Vibrasi': 'Vibration',
        'Lihat detail': 'View details',
        'DETAIL EQUIPMENT': 'EQUIPMENT DETAILS',
        'Data SO': 'SO Data',
        'Data CBM': 'CBM Data',
        'Status Operasi': 'Operating Status',
        'Tanggal Report': 'Report Date',
        'Durasi': 'Duration',
        'Tidak ada data WO.': 'No WO data.',
        'Belum tersedia': 'Not available'
    };

    function getLang() {
        return localStorage.getItem('sigap-lang') === 'en' ? 'en' : 'id';
    }

    function T(key, vars) {
        var lang = getLang();
        var text = (DICT[lang] && DICT[lang][key]);
        if (text === undefined) { text = (DICT.id && DICT.id[key]); }
        if (text === undefined) { return key; }
        if (vars) {
            Object.keys(vars).forEach(function (k) {
                text = text.split('{' + k + '}').join(vars[k]);
            });
        }
        return text;
    }

    function parseVars(el) {
        var raw = el.getAttribute('data-cbm-vars');
        if (!raw) { return null; }
        try { return JSON.parse(raw); } catch (e) { return null; }
    }

    function applyAuto(lang) {
        var root = document.querySelector('main') || document.body;
        root.querySelectorAll('*').forEach(function (el) {
            if (el.children.length) { return; }
            if (/^(SCRIPT|STYLE|OPTION|TEXTAREA|INPUT|SELECT)$/.test(el.tagName)) { return; }
            if (el.hasAttribute('data-i18n') || el.hasAttribute('data-cbm-i18n')) { return; }
            var orig = el.getAttribute('data-cbm-orig');
            var txt = orig !== null ? orig : el.textContent.trim();
            var en = AUTO[txt];
            if (en === undefined) { return; }
            if (orig === null) { el.setAttribute('data-cbm-orig', txt); }
            el.textContent = lang === 'en' ? en : txt;
        });
    }

    function apply() {
        var lang = getLang();

        document.querySelectorAll('[data-cbm-i18n]').forEach(function (el) {
            var key = el.getAttribute('data-cbm-i18n');
            var text = T(key, parseVars(el));
            if (text !== key) { el.innerHTML = text; }
        });

        document.querySelectorAll('[data-cbm-i18n-placeholder]').forEach(function (el) {
            var key = el.getAttribute('data-cbm-i18n-placeholder');
            var text = T(key);
            if (text !== key) { el.setAttribute('placeholder', text); }
        });

        applyAuto(lang);

        document.dispatchEvent(new CustomEvent('cbm:langapplied', { detail: { lang: lang } }));
    }

    window.CBM_T = T;
    window.CBM_APPLY_LANG = apply;

    document.addEventListener('sigap:langchange', apply);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apply);
    } else {
        apply();
    }
    // Terapkan juga setelah semua skrip halaman selesai (elemen yang dibuat belakangan).
    window.addEventListener('load', apply);
})();