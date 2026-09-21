@extends('layouts.app')

@section('title', $currentPlta['name'])

@section('page-title', $currentPlta['name'])

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Data PLTA</li>
    <li class="breadcrumb-item">{{ $currentPlta['name'] }}</li>
@endsection

@section('content')

    {{-- ======================================================
    PLTA HEADER INFO
    ====================================================== --}}
    <div class="plta-page-header" id="plta-header">
        <div class="plta-code-badge">{{ $currentPlta['code'] }}</div>
        <div class="plta-info">
            <div class="plta-info-name">{{ $currentPlta['name'] }}</div>
            <div class="plta-info-meta">
                <span>{{ $currentPlta['location'] }}</span>
                <span><span data-i18n="plta_capacity">Kapasitas:</span> {{ $currentPlta['capacity'] }}</span>
                <span>{{ count($equipments) }} <span data-i18n="plta_equipment_registered">Equipment Terdaftar</span></span>
            </div>
        </div>
    </div>

    {{-- ======================================================
    STATUS SUMMARY
    ====================================================== --}}
    <div class="plta-summary-bar" id="plta-status-summary">
        <div class="summary-chip normal">
            <span class="summary-chip-count">{{ $statusSummary['normal'] }}</span>
            <span data-i18n="status_normal">Normal</span>
        </div>
        <div class="summary-chip abnormal">
            <span class="summary-chip-count">{{ $statusSummary['abnormal'] }}</span>
            <span data-i18n="status_abnormal">Abnormal</span>
        </div>
        <div class="summary-chip not-ready">
            <span class="summary-chip-count">{{ $statusSummary['not_ready'] }}</span>
            <span data-i18n="status_not_ready">Not Ready</span>
        </div>
    </div>

    {{-- ======================================================
    FILTER BAR
    ====================================================== --}}
    <div class="filter-bar" id="equipment-filter-bar">
        <div class="filter-group">
            <span class="filter-label" data-i18n="plta_search_label">Cari:</span>
            <input type="text" class="form-control input-search" id="search-equipment"
                data-i18n-placeholder="plta_search_placeholder"
                placeholder="Cari equipment, KKS, ASSETNUM..." autocomplete="off">
        </div>
        <div class="filter-group" style="flex:0; min-width:auto;">
            <span class="filter-label" data-i18n="plta_unit_label">Unit:</span>
            <select class="form-control select-sm" id="filter-unit">
                <option value="" data-i18n="plta_all_units">Semua Unit</option>
                @php $units = array_unique(array_column($equipments, 'unit')); @endphp
                @foreach($units as $unit)
                    <option value="{{ $unit }}">{{ $unit }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group" style="flex:0; min-width:auto;">
            <span class="filter-label" data-i18n="plta_status_label">Status:</span>
            <select class="form-control select-sm" id="filter-status">
                <option value="" data-i18n="plta_all_status">Semua Status</option>
                <option value="Normal" data-i18n="status_normal">Normal</option>
                <option value="Abnormal" data-i18n="status_abnormal">Abnormal</option>
                <option value="Not Ready" data-i18n="status_not_ready">Not Ready</option>
            </select>
        </div>
        <div style="margin-left:auto;">
            <button class="btn btn-secondary btn-sm" id="btn-reset-filter" type="button" data-i18n="plta_reset_filter">
                ↺ Reset Filter
            </button>
        </div>
    </div>

    {{-- ======================================================
    TABEL EQUIPMENT
    ====================================================== --}}
    <div class="card" id="equipment-table-card">
        <div class="card-header">
            <div class="card-title">
                <span class="card-title-icon">⚙</span>
                <span data-i18n="plta_table_title">Daftar Equipment —</span> {{ $currentPlta['name'] }}
            </div>
            <div class="card-actions">
                <span id="equipment-count-label" style="font-size:11px; color:var(--text-muted);">
                    {{ count($equipments) }} <span data-i18n="plta_equipment_shown">equipment ditampilkan</span>
                </span>
            </div>
        </div>

        @if(count($equipments) > 0)
            <div class="table-wrapper">
                <table class="data-table" id="equipment-table">
                    <thead>
                        <tr>
                            <th class="col-no" data-i18n="plta_th_no">No</th>
                            <th data-i18n="plta_th_unit">Unit</th>
                            <th data-i18n="plta_th_system">System</th>
                            <th data-i18n="plta_th_equipment">Equipment</th>
                            <th data-i18n="plta_th_kks">KKS</th>
                            <th data-i18n="plta_th_assetnum">ASSETNUM</th>
                            <th data-i18n="plta_th_op_status">Operating Status</th>
                            <th data-i18n="plta_th_notes">Deskripsi</th>
                            <th data-i18n="plta_th_report_date">Tanggal Report</th>
                            <th data-i18n="plta_th_duration">Durasi</th>
                        </tr>
                    </thead>
                    <tbody id="equipment-tbody">
                        @foreach($equipments as $index => $eq)
                            <tr class="equipment-row" data-unit="{{ $eq['unit'] }}" data-status="{{ $eq['status_operasi'] }}"
                                data-search="{{ strtolower($eq['equipment'] . ' ' . $eq['kks'] . ' ' . $eq['assetnum'] . ' ' . $eq['unit'] . ' ' . $eq['system']) }}">
                                <td class="col-no">{{ $index + 1 }}</td>
                                <td>
                                    <span
                                        style="font-size:11.5px; font-weight:500; color:var(--text-secondary);">{{ $eq['unit'] }}</span>
                                </td>
                                <td>
                                    <span style="font-size:12px;">{{ $eq['system'] }}</span>
                                </td>
                                <td>
                                    <span style="font-size:12.5px; font-weight:500;">{{ $eq['equipment'] }}</span>
                                </td>
                                <td class="col-kks">{{ $eq['kks'] }}</td>
                                <td class="col-assetnum">{{ $eq['assetnum'] }}</td>
                                <td>
                                    @php
                                        $statusSelectClass = match ($eq['status_operasi']) {
                                            'Normal' => 'normal-select',
                                            'Abnormal' => 'abnormal-select',
                                            default => 'notready-select',
                                        };
                                    @endphp
                                    @if($canEditStatus)
                                        {{-- SO / CBM: dropdown interaktif dengan AJAX save --}}
                                        <select class="status-select {{ $statusSelectClass }}"
                                            id="status-{{ $eq['assetnum'] }}"
                                            data-assetnum="{{ $eq['assetnum'] }}"
                                            data-update-url="{{ route('equipment.update-status', $eq['assetnum']) }}"
                                            title="Change Equipment Operating Status">
                                            <option value="Normal" {{ $eq['status_operasi'] === 'Normal' ? 'selected' : '' }}>Normal</option>
                                            <option value="Abnormal" {{ $eq['status_operasi'] === 'Abnormal' ? 'selected' : '' }}>Abnormal</option>
                                            <option value="Not Ready" {{ $eq['status_operasi'] === 'Not Ready' ? 'selected' : '' }}>Not Ready</option>
                                        </select>
                                    @else
                                        {{-- Guest / Reviewer: read-only, klik memunculkan modal login --}}
                                        <span class="status-select {{ $statusSelectClass }} status-select-readonly"
                                            id="status-{{ $eq['assetnum'] }}"
                                            data-assetnum="{{ $eq['assetnum'] }}"
                                            data-requires-auth="true"
                                            data-feature="Change Operating Status"
                                            title="Log in as SO or CBM to change the status"
                                            role="button"
                                            tabindex="0"
                                            aria-label="Status: {{ $eq['status_operasi'] }}. Login required to change it.">{{ $eq['status_operasi'] }}</span>
                                    @endif
                                </td>
                                <td class="keterangan-cell">
                                    @if(count($eq['keterangan']) > 0)
                                        @foreach($eq['keterangan'] as $wo)
                                            <div class="keterangan-wo">{{ $wo['no_wo'] }}</div>
                                            <div class="keterangan-desc">{{ $wo['description'] }}</div>
                                            <span class="keterangan-status">{{ $wo['status'] }}</span>
                                        @endforeach
                                    @else
                                        <div style="font-size:12px; color:var(--text-muted); font-style:italic;">
                                            —
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:12.5px; white-space:nowrap;">
                                    {{ $eq['report_date'] }}
                                </td>
                                <td style="font-size:12.5px; white-space:nowrap;">
                                    {{ $eq['durasi_hari'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Empty state saat filter kosong --}}
            <div id="no-results" class="empty-state" style="display:none;">
                <div class="empty-state-icon">○</div>
                <div class="empty-state-title" data-i18n="plta_no_data_title">Tidak ada data yang cocok</div>
                <div class="empty-state-text" data-i18n="plta_no_data_text">Coba ubah kata kunci pencarian atau filter.</div>
            </div>

        @else
            <div class="empty-state">
                <div class="empty-state-icon">⚙</div>
                <div class="empty-state-title" data-i18n="plta_empty_title">Belum ada data equipment</div>
                <div class="empty-state-text">
                    <span data-i18n="plta_empty_text">Data equipment untuk</span>
                    {{ $currentPlta['name'] }}
                    <span data-i18n="plta_empty_text2">belum tersedia.</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Navigasi antar PLTA --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; flex-wrap:wrap; gap:8px;">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" id="btn-back-dashboard" data-i18n="plta_back_dashboard">
            ← Kembali ke Dashboard
        </a>
        <div style="display:flex; gap:8px;">
            @php
                $pltaSlugs = collect($pltaList)->pluck('slug')->all();
                $currentIndex = array_search($currentPlta['slug'], $pltaSlugs);
                $prevSlug = $currentIndex > 0 ? $pltaSlugs[$currentIndex - 1] : null;
                $nextSlug = $currentIndex < count($pltaSlugs) - 1 ? $pltaSlugs[$currentIndex + 1] : null;
            @endphp
            @if($prevSlug)
                <a href="{{ route('plta.show', $prevSlug) }}" class="btn btn-secondary btn-sm" id="btn-prev-plta" data-i18n="plta_prev">
                    ← PLTA Sebelumnya
                </a>
            @endif
            @if($nextSlug)
                <a href="{{ route('plta.show', $nextSlug) }}" class="btn btn-primary btn-sm" id="btn-next-plta" data-i18n="plta_next">
                    PLTA Berikutnya →
                </a>
            @endif
        </div>
    </div>

@endsection

@section('scripts')
    <style>
        /* Read-only status badge untuk Guest/Reviewer */
        .status-select-readonly {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: default;
            user-select: none;
            pointer-events: auto;
        }
        /* Toast notifikasi simpan status */
        #status-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 4px 18px rgba(0,0,0,0.18);
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.22s ease, transform 0.22s ease;
            pointer-events: none;
        }
        #status-toast.show {
            opacity: 1;
            transform: translateY(0);
        }
        #status-toast.toast-success {
            background: #22c55e;
            color: #fff;
        }
        #status-toast.toast-error {
            background: #ef4444;
            color: #fff;
        }
    </style>

    {{-- Toast element --}}
    <div id="status-toast" role="alert" aria-live="polite"></div>

    <script>
        // ============================================================
        // TOAST HELPER
        // ============================================================
        (function () {
            window.showStatusToast = function (message, type) {
                const toast = document.getElementById('status-toast');
                if (!toast) { return; }
                toast.textContent = message;
                toast.className = 'show ' + (type === 'error' ? 'toast-error' : 'toast-success');
                clearTimeout(toast._timer);
                toast._timer = setTimeout(function () {
                    toast.className = toast.className.replace('show', '').trim();
                }, 3200);
            };
        })();

        // ============================================================
        // FILTER & SEARCH
        // ============================================================
        (function () {
            const searchInput = document.getElementById('search-equipment');
            const unitFilter  = document.getElementById('filter-unit');
            const statusFilter = document.getElementById('filter-status');
            const resetBtn    = document.getElementById('btn-reset-filter');
            const rows        = document.querySelectorAll('.equipment-row');
            const noResults   = document.getElementById('no-results');
            const countLabel  = document.getElementById('equipment-count-label');

            function applyFilters() {
                const searchVal  = searchInput.value.toLowerCase().trim();
                const unitVal    = unitFilter.value;
                const statusVal  = statusFilter.value;
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matchSearch = !searchVal || row.dataset.search.includes(searchVal);
                    const matchUnit   = !unitVal   || row.dataset.unit === unitVal;
                    const matchStatus = !statusVal || row.dataset.status === statusVal;

                    if (matchSearch && matchUnit && matchStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                noResults.style.display = visibleCount === 0 ? 'block' : 'none';

                // Update count label with i18n
                const lang = localStorage.getItem('sigap-lang') === 'en' ? 'en' : 'id';
                const shownLabel = (window.SIGAP_I18N && window.SIGAP_I18N[lang])
                    ? window.SIGAP_I18N[lang].plta_equipment_shown
                    : 'equipment ditampilkan';
                countLabel.innerHTML = visibleCount + ' <span data-i18n="plta_equipment_shown">' + shownLabel + '</span>';
            }

            if (searchInput)  { searchInput.addEventListener('input', applyFilters); }
            if (unitFilter)   { unitFilter.addEventListener('change', applyFilters); }
            if (statusFilter) { statusFilter.addEventListener('change', applyFilters); }

            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    searchInput.value  = '';
                    unitFilter.value   = '';
                    statusFilter.value = '';
                    applyFilters();
                });
            }

            // Sinkronisasi data-status pada row saat select diubah
            document.querySelectorAll('select.status-select').forEach(function (select) {
                select.addEventListener('change', function () {
                    const row = this.closest('tr');
                    if (row) { row.dataset.status = this.value; }
                    applyFilters();
                });
            });

            // Re-apply label on lang change
            document.addEventListener('sigap:langchange', function () {
                applyFilters();
            });
        })();

        // ============================================================
        // AJAX SAVE — hanya berjalan untuk SO/CBM (select elements)
        // ============================================================
        (function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]') &&
                document.querySelector('meta[name="csrf-token"]').getAttribute('content') ||
                '{{ csrf_token() }}';

            document.querySelectorAll('select.status-select').forEach(function (select) {
                const originalValue = select.value;

                select.addEventListener('change', function () {
                    const newStatus = this.value;
                    const updateUrl = this.dataset.updateUrl;
                    const assetnum  = this.dataset.assetnum;

                    if (!updateUrl) { return; }

                    select.disabled = true;

                    fetch(updateUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ status: newStatus }),
                    })
                    .then(function (response) {
                        if (!response.ok) {
                            return response.json().then(function (data) {
                                throw new Error(data.error || window.SIGAP_T('plta_toast_error'));
                            });
                        }
                        return response.json();
                    })
                    .then(function () {
                        window.showStatusToast(assetnum + ' ' + window.SIGAP_T('plta_toast_success') + ' ' + newStatus, 'success');
                    })
                    .catch(function (err) {
                        select.value = select.dataset.prevValue || originalValue;
                        const prev = select.value;
                        select.classList.remove('normal-select', 'abnormal-select', 'notready-select');
                        if (prev === 'Normal')   { select.classList.add('normal-select'); }
                        else if (prev === 'Abnormal') { select.classList.add('abnormal-select'); }
                        else { select.classList.add('notready-select'); }

                        window.showStatusToast(err.message || window.SIGAP_T('plta_toast_error'), 'error');
                    })
                    .finally(function () {
                        select.disabled = false;
                        select.dataset.prevValue = select.value;
                        const row = select.closest('tr');
                        if (row) { row.dataset.status = select.value; }
                    });
                });
            });
        })();

        // ============================================================
        // GUEST / REVIEWER — intercept klik pada read-only status span
        // ============================================================
        (function () {
            const backdrop = document.getElementById('auth-modal-backdrop');
            if (!backdrop) { return; }
            // Span read-only sudah punya data-requires-auth="true" sehingga
            // akan otomatis di-intercept oleh listener global di app.blade.php.
        })();
    </script>
@endsection