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
                <span>Kapasitas: {{ $currentPlta['capacity'] }}</span>
                <span>{{ count($equipments) }} Equipment Terdaftar</span>
            </div>
        </div>
    </div>

    {{-- ======================================================
    STATUS SUMMARY
    ====================================================== --}}
    <div class="plta-summary-bar" id="plta-status-summary">
        <div class="summary-chip normal">
            <span class="summary-chip-count">{{ $statusSummary['normal'] }}</span>
            <span>Normal</span>
        </div>
        <div class="summary-chip abnormal">
            <span class="summary-chip-count">{{ $statusSummary['abnormal'] }}</span>
            <span>Abnormal</span>
        </div>
        <div class="summary-chip not-ready">
            <span class="summary-chip-count">{{ $statusSummary['not_ready'] }}</span>
            <span>Not Ready</span>
        </div>
    </div>

    {{-- ======================================================
    FILTER BAR
    ====================================================== --}}
    <div class="filter-bar" id="equipment-filter-bar">
        <div class="filter-group">
            <span class="filter-label">Cari:</span>
            <input type="text" class="form-control input-search" id="search-equipment"
                placeholder="Cari equipment, KKS, ASSETNUM..." autocomplete="off">
        </div>
        <div class="filter-group" style="flex:0; min-width:auto;">
            <span class="filter-label">Unit:</span>
            <select class="form-control select-sm" id="filter-unit">
                <option value="">Semua Unit</option>
                @php $units = array_unique(array_column($equipments, 'unit')); @endphp
                @foreach($units as $unit)
                    <option value="{{ $unit }}">{{ $unit }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group" style="flex:0; min-width:auto;">
            <span class="filter-label">Status:</span>
            <select class="form-control select-sm" id="filter-status">
                <option value="">Semua Status</option>
                <option value="Normal">Normal</option>
                <option value="Abnormal">Abnormal</option>
                <option value="Not Ready">Not Ready</option>
            </select>
        </div>
        <div style="margin-left:auto;">
            <button class="btn btn-secondary btn-sm" id="btn-reset-filter" type="button">
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
                Daftar Equipment — {{ $currentPlta['name'] }}
            </div>
            <div class="card-actions">
                <span id="equipment-count-label" style="font-size:11px; color:var(--text-muted);">
                    {{ count($equipments) }} equipment ditampilkan
                </span>
            </div>
        </div>

        @if(count($equipments) > 0)
            <div class="table-wrapper">
                <table class="data-table" id="equipment-table">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Unit</th>
                            <th>System</th>
                            <th>Equipment</th>
                            <th>KKS</th>
                            <th>ASSETNUM</th>
                            <th>Status Operasi</th>
                            <th>Keterangan</th>
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
                                    <select class="status-select {{ $statusSelectClass }}" id="status-{{ $eq['assetnum'] }}"
                                        data-assetnum="{{ $eq['assetnum'] }}" title="Ubah Status Operasi Equipment">
                                        <option value="Normal" {{ $eq['status_operasi'] === 'Normal' ? 'selected' : '' }}>Normal
                                        </option>
                                        <option value="Abnormal" {{ $eq['status_operasi'] === 'Abnormal' ? 'selected' : '' }}>Abnormal
                                        </option>
                                        <option value="Not Ready" {{ $eq['status_operasi'] === 'Not Ready' ? 'selected' : '' }}>Not
                                            Ready</option>
                                    </select>
                                </td>
                                <td class="keterangan-cell">
                                    @if($eq['keterangan']['no_wo'] !== '-')
                                        <div class="keterangan-wo">{{ $eq['keterangan']['no_wo'] }}</div>
                                        <div class="keterangan-desc">{{ $eq['keterangan']['description'] }}</div>
                                        <span class="keterangan-status">{{ $eq['keterangan']['status'] }}</span>
                                    @else
                                        <div style="font-size:12px; color:var(--text-muted); font-style:italic;">
                                            {{ $eq['keterangan']['description'] }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Empty state saat filter kosong --}}
            <div id="no-results" class="empty-state" style="display:none;">
                <div class="empty-state-icon">○</div>
                <div class="empty-state-title">Tidak ada data yang cocok</div>
                <div class="empty-state-text">Coba ubah kata kunci pencarian atau filter.</div>
            </div>

        @else
            <div class="empty-state">
                <div class="empty-state-icon">⚙</div>
                <div class="empty-state-title">Belum ada data equipment</div>
                <div class="empty-state-text">Data equipment untuk {{ $currentPlta['name'] }} belum tersedia.</div>
            </div>
        @endif
    </div>

    {{-- Navigasi antar PLTA --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; flex-wrap:wrap; gap:8px;">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" id="btn-back-dashboard">
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
                <a href="{{ route('plta.show', $prevSlug) }}" class="btn btn-secondary btn-sm" id="btn-prev-plta">
                    ← PLTA Sebelumnya
                </a>
            @endif
            @if($nextSlug)
                <a href="{{ route('plta.show', $nextSlug) }}" class="btn btn-primary btn-sm" id="btn-next-plta">
                    PLTA Berikutnya →
                </a>
            @endif
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // Filter & Search Logic
        (function () {
            const searchInput = document.getElementById('search-equipment');
            const unitFilter = document.getElementById('filter-unit');
            const statusFilter = document.getElementById('filter-status');
            const resetBtn = document.getElementById('btn-reset-filter');
            const rows = document.querySelectorAll('.equipment-row');
            const noResults = document.getElementById('no-results');
            const countLabel = document.getElementById('equipment-count-label');

            function applyFilters() {
                const searchVal = searchInput.value.toLowerCase().trim();
                const unitVal = unitFilter.value;
                const statusVal = statusFilter.value;
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matchSearch = !searchVal || row.dataset.search.includes(searchVal);
                    const matchUnit = !unitVal || row.dataset.unit === unitVal;
                    const matchStatus = !statusVal || row.dataset.status === statusVal;

                    if (matchSearch && matchUnit && matchStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
                countLabel.textContent = visibleCount + ' equipment ditampilkan';
            }

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (unitFilter) unitFilter.addEventListener('change', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);

            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    unitFilter.value = '';
                    statusFilter.value = '';
                    applyFilters();
                });
            }

            // Update row data-status saat select diubah
            document.querySelectorAll('.status-select').forEach(function (select) {
                select.addEventListener('change', function () {
                    const row = this.closest('tr');
                    if (row) { row.dataset.status = this.value; }
                    // Reapply filters
                    applyFilters();
                });
            });
        })();
    </script>
@endsection