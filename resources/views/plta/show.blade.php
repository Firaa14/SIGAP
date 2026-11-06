@extends('layouts.app')

@section('title', $currentPlta['name'])

@section('page-title', $currentPlta['name'])

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Data PLTA</li>
    <li class="breadcrumb-item">{{ $currentPlta['name'] }}</li>
@endsection

@push('styles')
<style>
    /* Chip ringkasan status CBM (hanya tampil di role CBM) */
    .cbm-chip-n  { background:#14B8A622; color:#0D9488; border-color:#14B8A655; }
    .cbm-chip-l  { background:#22C55E22; color:#16A34A; border-color:#22C55E55; }
    .cbm-chip-m  { background:#EAB30822; color:#CA8A04; border-color:#EAB30855; }
    .cbm-chip-h  { background:#8B5CF622; color:#7C3AED; border-color:#8B5CF655; }
    .cbm-chip-c  { background:#EF444422; color:#DC2626; border-color:#EF444455; }
    .cbm-chip-nm { background:#64748B22; color:#64748B; border-color:#64748B55; }
</style>
@endpush

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
        @if($isCbm)
            @php
                $cbmChips = [
                    'N'  => ['label' => 'Normal',       'class' => 'cbm-chip-n'],
                    'L'  => ['label' => 'Low',          'class' => 'cbm-chip-l'],
                    'M'  => ['label' => 'Medium',       'class' => 'cbm-chip-m'],
                    'H'  => ['label' => 'High',         'class' => 'cbm-chip-h'],
                    'C'  => ['label' => 'Critical',     'class' => 'cbm-chip-c'],
                    'NM' => ['label' => 'Not Measured', 'class' => 'cbm-chip-nm'],
                ];
            @endphp
            @foreach($cbmChips as $code => $chip)
                <div class="summary-chip cbm-chip {{ $chip['class'] }}">
                    <span class="summary-chip-count">{{ $statusSummary[$code] }}</span>
                    {{ $code === 'NM' ? $chip['label'] : $code . ' - ' . $chip['label'] }}
                </div>
            @endforeach
        @else
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
        @endif
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
                @if($isCbm)
                    <option value="N">N - Normal</option>
                    <option value="L">L - Low</option>
                    <option value="M">M - Medium</option>
                    <option value="H">H - High</option>
                    <option value="C">C - Critical</option>
                    <option value="NM">Not Measured</option>
                @else
                    <option value="Normal" data-i18n="status_normal">Normal</option>
                    <option value="Abnormal" data-i18n="status_abnormal">Abnormal</option>
                    <option value="Not Ready" data-i18n="status_not_ready">Not Ready</option>
                @endif
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
    @if($isCbm)
        @php
            // Parameter CBM yang sudah pernah diupload (minimal 1 data di database).
            // Parameter yang belum pernah diupload -> "SOON".
            // Parameter yang sudah diupload tapi equipment ini tidak punya data -> "Not Measured".
            $uploadedParameters = \Illuminate\Support\Facades\Schema::hasTable('cbm_measurements')
                ? \Illuminate\Support\Facades\DB::table('cbm_measurements')->distinct()->pluck('parameter')->all()
                : [];
        @endphp
        <div class="card" id="cbm-equipment-table-card">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-title-icon">⚙</span>
                    <span>Data Pengujian CBM — {{ $currentPlta['name'] }}</span>
                </div>
                <div class="card-actions">
                    <span id="equipment-count-label" style="font-size:11px;color:var(--text-muted);">
                        {{ $cbmEquipments->count() }} equipment ditampilkan
                    </span>
                </div>
            </div>

            @if($cbmEquipments->isNotEmpty())
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Unit</th>
                                <th>System</th>
                                <th>Equipment</th>
                                <th>KKS</th>
                                <th>ASSETNUM</th>
                                <th>Thermograph</th>
                                <th>Vibrasi</th>
                                <th>Oil Analysis</th>
                                <th>MCA</th>
                                <th>PD</th>
                                <th>DGA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cbmEquipments as $index => $eq)
                                @php
                                    $thermo = in_array($eq['thermograph'], ['N', 'L', 'M', 'H', 'C'], true)
                                        ? $eq['thermograph']
                                        : 'NM';
                                @endphp
                                <tr class="equipment-row"
                                    data-unit="{{ $eq['unit'] }}"
                                    data-status="{{ $thermo }}"
                                    data-search="{{ strtolower($eq['equipment'] . ' ' . $eq['kks'] . ' ' . $eq['assetnum'] . ' ' . $eq['unit'] . ' ' . $eq['system']) }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $eq['unit'] }}</td>
                                    <td>{{ $eq['system'] }}</td>
                                    <td>{{ $eq['equipment'] }}</td>
                                    <td>{{ $eq['kks'] }}</td>
                                    <td>{{ $eq['assetnum'] }}</td>
                                    @foreach(['thermograph', 'vibrasi', 'oil_analysis', 'mca', 'pd', 'dga'] as $parameter)
                                        <td>
                                            @php
                                                $statusMeta = $cbmStatusLegend[$parameter][$eq[$parameter]] ?? null;
                                                $isSoon = $eq[$parameter] === 'SOON';
                                                // Sudah pernah diupload tapi equipment ini tidak ada datanya -> Not Measured
                                                $soonText = in_array($parameter, $uploadedParameters, true) ? 'Not Measured' : 'SOON';
                                            @endphp
                                            <span class="cbm-status-badge cbm-status-{{ strtolower($eq[$parameter]) }}"
                                                @if($statusMeta) style="background:{{ $statusMeta['color'] }}22;color:{{ $statusMeta['color'] }};" @endif
                                                title="{{ $statusMeta['label'] ?? $soonText }}">
                                                {{ $isSoon ? $soonText : $eq[$parameter] }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">⚙</div>
                    <div class="empty-state-title">Belum ada data equipment</div>
                    <div class="empty-state-text">Data pengujian CBM untuk {{ $currentPlta['name'] }} belum tersedia.</div>
                </div>
            @endif

            {{-- Empty state saat filter kosong (CBM) --}}
            <div id="no-results" class="empty-state" style="display:none;">
                <div class="empty-state-icon">○</div>
                <div class="empty-state-title" data-i18n="plta_no_data_title">Tidak ada data yang cocok</div>
                <div class="empty-state-text" data-i18n="plta_no_data_text">Coba ubah kata kunci pencarian atau filter.</div>
            </div>
        </div>
    @else
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
                            <th data-i18n="plta_th_op_status">Status Operasi</th>
                            @if(!$isReadOnlyView)
                                <th data-i18n="plta_th_notes">Deskripsi</th>
                                <th data-i18n="plta_th_report_date">Tanggal Report</th>
                                <th data-i18n="plta_th_duration">Durasi</th>
                            @endif
                            @if($isReadOnlyView)
                                <th>Action</th>
                            @endif
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
                                @if(!$isReadOnlyView)
                                    <td class="keterangan-cell">
                                        @foreach($eq['keterangan'] as $wo)
                                            <div class="keterangan-wo">{{ $wo['no_wo'] }}</div>
                                            <div class="keterangan-desc">{{ $wo['description'] }}</div>
                                            <span class="keterangan-status">{{ $wo['status'] }}</span>
                                        @endforeach
                                    </td>
                                    <td style="font-size:12.5px; white-space:nowrap;">
                                        {{ $eq['report_date'] }}
                                    </td>
                                    <td style="font-size:12.5px; white-space:nowrap;">
                                        {{ $eq['durasi_hari'] }}
                                    </td>
                                @endif
                                @if($isReadOnlyView)
                                    @php
                                        $cbmEq = $cbmEquipments->firstWhere('assetnum', $eq['assetnum']);
                                        $cbmMeta = $cbmEq ? [
                                            'thermograph' => $cbmStatusLegend['thermograph'][$cbmEq['thermograph']] ?? null,
                                            'vibrasi' => $cbmStatusLegend['vibrasi'][$cbmEq['vibrasi']] ?? null,
                                            'oil_analysis' => $cbmStatusLegend['oil_analysis'][$cbmEq['oil_analysis']] ?? null,
                                            'dga' => $cbmStatusLegend['dga'][$cbmEq['dga']] ?? null,
                                        ] : [];
                                    @endphp
                                    <td class="equipment-detail-action">
                                        <button type="button" class="equipment-detail-btn"
                                            aria-label="Lihat detail {{ $eq['equipment'] }}"
                                            title="Lihat detail {{ $eq['equipment'] }}"
                                            data-assetnum="{{ $eq['assetnum'] }}"
                                            data-equipment="{{ $eq['equipment'] }}"
                                            data-status="{{ $eq['status_operasi'] }}"
                                            data-report-date="{{ $eq['report_date'] }}"
                                            data-duration="{{ $eq['durasi_hari'] }}"
                                            data-so-status-class="{{ $statusSelectClass }}"
                                            data-work-orders="{{ base64_encode(json_encode($eq['keterangan'], JSON_UNESCAPED_UNICODE)) }}"
                                            data-thermograph="{{ $cbmEq['thermograph'] ?? 'SOON' }}"
                                            data-thermograph-color="{{ $cbmMeta['thermograph']['color'] ?? '#64748B' }}"
                                            data-thermograph-label="{{ $cbmMeta['thermograph']['label'] ?? 'Belum tersedia' }}"
                                            data-vibrasi="{{ $cbmEq['vibrasi'] ?? 'SOON' }}"
                                            data-vibrasi-color="{{ $cbmMeta['vibrasi']['color'] ?? '#64748B' }}"
                                            data-vibrasi-label="{{ $cbmMeta['vibrasi']['label'] ?? 'Belum tersedia' }}"
                                            data-oil-analysis="{{ $cbmEq['oil_analysis'] ?? 'SOON' }}"
                                            data-oil-analysis-color="{{ $cbmMeta['oil_analysis']['color'] ?? '#64748B' }}"
                                            data-oil-analysis-label="{{ $cbmMeta['oil_analysis']['label'] ?? 'Belum tersedia' }}"
                                            data-mca="{{ $cbmEq['mca'] ?? 'SOON' }}"
                                            data-pd="{{ $cbmEq['pd'] ?? 'SOON' }}"
                                            data-dga="{{ $cbmEq['dga'] ?? 'SOON' }}"
                                            data-dga-color="{{ $cbmMeta['dga']['color'] ?? '#64748B' }}"
                                            data-dga-label="{{ $cbmMeta['dga']['label'] ?? 'Belum tersedia' }}">
                                            <span class="equipment-detail-btn-icon" aria-hidden="true">↗</span>
                                            <span>Lihat detail</span>
                                        </button>
                                    </td>
                                @endif
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
    @endif

    @if($isReadOnlyView)
        <div id="equipment-detail-modal" class="equipment-detail-modal" hidden>
            <div class="equipment-detail-dialog" role="dialog" aria-modal="true" aria-labelledby="equipment-detail-title">
                <button type="button" class="equipment-detail-close" aria-label="Tutup detail">&times;</button>
                <span class="equipment-detail-eyebrow">DETAIL EQUIPMENT</span>
                <h2 id="equipment-detail-title" data-detail="assetnum">—</h2>
                <p class="equipment-detail-name" data-detail="equipment">—</p>

                <section class="equipment-detail-section">
                    <h3>Data SO</h3>
                    <div class="equipment-detail-grid">
                        <div>
                            <span>Status Operasi</span>
                            <strong class="detail-status-badge" data-detail="status">—</strong>
                        </div>
                        <div><span>Tanggal Report</span><strong data-detail="report-date">—</strong></div>
                        <div><span>Durasi</span><strong data-detail="duration">—</strong></div>
                    </div>
                    <div class="equipment-work-orders" data-detail-work-orders>
                        <span class="equipment-detail-empty">Tidak ada data WO.</span>
                    </div>
                </section>

                <section class="equipment-detail-section">
                    <h3>Data CBM</h3>
                    <div class="equipment-detail-grid">
                        <div><span>Thermograph</span><strong class="detail-cbm-badge" data-detail="thermograph">—</strong></div>
                        <div><span>Vibrasi</span><strong class="detail-cbm-badge" data-detail="vibrasi">—</strong></div>
                        <div><span>Oil Analysis</span><strong class="detail-cbm-badge" data-detail="oil-analysis">—</strong></div>
                        <div><span>MCA</span><strong class="detail-cbm-badge detail-soon" data-detail="mca">—</strong></div>
                        <div><span>PD</span><strong class="detail-cbm-badge detail-soon" data-detail="pd">—</strong></div>
                        <div><span>DGA</span><strong class="detail-cbm-badge" data-detail="dga">—</strong></div>
                    </div>
                </section>
            </div>
        </div>
    @endif

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
        .cbm-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            padding: 3px 7px;
            border-radius: 6px;
            background: var(--color-blue-light);
            color: var(--color-accent);
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .cbm-status-soon {
            background: #F1F5F9;
            color: #64748B;
        }

        .equipment-detail-modal[hidden] {
            display: none;
        }

        .equipment-detail-modal {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.58);
        }

        .equipment-detail-dialog {
            position: relative;
            width: min(720px, 100%);
            max-height: 90vh;
            overflow-y: auto;
            padding: 28px;
            border-radius: 16px;
            background: #FFFFFF !important;
            opacity: 1 !important;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.28);
        }

        .equipment-detail-eyebrow {
            color: var(--color-accent);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
        }

        .equipment-detail-name {
            margin: -12px 0 22px;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
        }

        .equipment-detail-dialog h2 {
            margin: 6px 0 16px;
            color: var(--text-primary);
        }

        .equipment-detail-close {
            position: absolute;
            top: 12px;
            right: 16px;
            border: 0;
            background: transparent;
            color: var(--text-secondary);
            font-size: 26px;
            cursor: pointer;
        }

        .equipment-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .equipment-detail-grid div {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: #F7F9FC !important;
            opacity: 1 !important;
        }

        .equipment-detail-grid span {
            color: var(--text-muted);
            font-size: 11px;
        }

        .equipment-detail-grid strong {
            color: var(--text-primary);
            font-size: 13px;
        }

        .equipment-detail-section {
            margin-top: 20px;
        }

        .equipment-detail-section h3 {
            margin: 0 0 10px;
            color: var(--text-primary);
            font-size: 14px;
        }

        .detail-status-badge,
        .detail-cbm-badge {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            border: 1px solid currentColor;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px !important;
        }

        .detail-status-badge.normal-select {
            background: var(--status-normal-bg);
            color: var(--status-normal-text);
        }

        .detail-status-badge.abnormal-select {
            background: var(--status-abnormal-bg);
            color: var(--status-abnormal-text);
        }

        .detail-status-badge.notready-select {
            background: var(--status-notready-bg);
            color: var(--status-notready-text);
        }

        .equipment-work-orders {
            display: grid;
            gap: 10px;
            margin-top: 12px;
        }

        .equipment-work-order {
            padding: 12px;
            border: 1px solid var(--border-color);
            border-left: 3px solid var(--color-accent);
            border-radius: 8px;
            background: var(--bg-surface-2);
        }

        .equipment-work-order-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: var(--color-accent);
            font-size: 12px;
            font-weight: 700;
        }

        .equipment-work-order-description {
            margin-top: 6px;
            color: var(--text-secondary);
            font-size: 12px;
            line-height: 1.5;
        }

        .equipment-work-order-type {
            margin-top: 8px;
            color: var(--text-muted);
            font-size: 11px;
        }

        .equipment-detail-empty {
            color: var(--text-muted);
            font-size: 12px;
            font-style: italic;
        }

        .equipment-detail-action {
            width: 1%;
            min-width: 128px;
            text-align: center;
            white-space: nowrap;
        }

        .equipment-detail-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 34px;
            padding: 8px 12px;
            border: 1px solid #B9D7F5;
            border-radius: 8px;
            background: #F1F7FE;
            color: #0052A8;
            box-shadow: 0 1px 2px rgba(0, 59, 123, 0.08);
            font-family: inherit;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            white-space: nowrap;
            transition: background-color .18s ease, border-color .18s ease,
                box-shadow .18s ease, color .18s ease, transform .18s ease;
        }

        .equipment-detail-btn-icon {
            display: inline-grid;
            width: 17px;
            height: 17px;
            place-items: center;
            border-radius: 5px;
            background: #DCEEFF;
            font-size: 14px;
            line-height: 1;
        }

        .equipment-detail-btn:hover {
            border-color: #0066CC;
            background: #0066CC;
            color: #FFFFFF;
            box-shadow: 0 4px 10px rgba(0, 102, 204, 0.22);
            transform: translateY(-1px);
        }

        .equipment-detail-btn:hover .equipment-detail-btn-icon {
            background: rgba(255, 255, 255, 0.18);
        }

        .equipment-detail-btn:focus-visible {
            outline: 3px solid rgba(0, 102, 204, 0.24);
            outline-offset: 2px;
        }

        .equipment-detail-btn:active {
            box-shadow: 0 1px 3px rgba(0, 59, 123, 0.14);
            transform: translateY(0);
        }

        @media (max-width: 600px) {
            .equipment-detail-grid { grid-template-columns: 1fr; }
            .equipment-detail-action { min-width: 112px; }
            .equipment-detail-btn { padding: 8px 9px; }
        }

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

        // ============================================================
        // PUBLIC EQUIPMENT DETAIL
        // ============================================================
        (function () {
            const modal = document.getElementById('equipment-detail-modal');
            if (!modal) { return; }

            const close = function () {
                modal.hidden = true;
                document.body.style.overflow = '';
            };

            document.querySelectorAll('.equipment-detail-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    const setDetail = function (key, value) {
                        const target = modal.querySelector('[data-detail="' + key.replace(/[A-Z]/g, function (letter) {
                            return '-' + letter.toLowerCase();
                        }) + '"]');
                        if (target) { target.textContent = value || '—'; }
                    };

                    setDetail('assetnum', button.dataset.assetnum);
                    setDetail('equipment', button.dataset.equipment);
                    setDetail('status', button.dataset.status);
                    setDetail('reportDate', button.dataset.reportDate);
                    setDetail('duration', button.dataset.duration);

                    const statusBadge = modal.querySelector('[data-detail="status"]');
                    statusBadge.classList.remove('normal-select', 'abnormal-select', 'notready-select');
                    statusBadge.classList.add(button.dataset.soStatusClass || 'notready-select');

                    ['thermograph', 'vibrasi', 'oilAnalysis', 'mca', 'pd', 'dga'].forEach(function (key) {
                        setDetail(key, button.dataset[key]);
                        const badge = modal.querySelector('[data-detail="' + key.replace(/[A-Z]/g, function (letter) {
                            return '-' + letter.toLowerCase();
                        }) + '"]');
                        if (!badge) { return; }
                        badge.classList.remove('detail-soon');
                        badge.style.color = button.dataset[key + 'Color'] || '#64748B';
                        badge.style.backgroundColor = (button.dataset[key + 'Color'] || '#64748B') + '1A';
                        badge.title = button.dataset[key + 'Label'] || 'Belum tersedia';
                        if ((button.dataset[key] || '') === 'SOON') {
                            badge.classList.add('detail-soon');
                            badge.style.color = '#64748B';
                            badge.style.backgroundColor = '#F1F5F9';
                        }
                    });

                    const workOrders = modal.querySelector('[data-detail-work-orders]');
                    workOrders.textContent = '';
                    let orders = [];
                    try {
                        const binary = atob(button.dataset.workOrders || '');
                        const bytes = Array.prototype.map.call(binary, function (char) {
                            return '%' + ('00' + char.charCodeAt(0).toString(16)).slice(-2);
                        }).join('');
                        orders = JSON.parse(decodeURIComponent(bytes));
                    } catch (error) {
                        orders = [];
                    }

                    if (!orders.length) {
                        const empty = document.createElement('span');
                        empty.className = 'equipment-detail-empty';
                        empty.textContent = 'Tidak ada data WO.';
                        workOrders.appendChild(empty);
                    } else {
                        orders.forEach(function (order) {
                            const card = document.createElement('article');
                            card.className = 'equipment-work-order';
                            const head = document.createElement('div');
                            head.className = 'equipment-work-order-head';
                            const number = document.createElement('span');
                            number.textContent = order.no_wo || '-';
                            const type = document.createElement('span');
                            type.textContent = order.status || '—';
                            head.append(number, type);
                            const description = document.createElement('div');
                            description.className = 'equipment-work-order-description';
                            description.textContent = order.description || '—';
                            const typeLabel = document.createElement('div');
                            typeLabel.className = 'equipment-work-order-type';
                            typeLabel.textContent = 'Tipe: ' + (order.status || '—');
                            card.append(head, description, typeLabel);
                            workOrders.appendChild(card);
                        });
                    }

                    modal.hidden = false;
                    document.body.style.overflow = 'hidden';
                });
            });

            modal.querySelector('.equipment-detail-close').addEventListener('click', close);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) { close(); }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.hidden) { close(); }
            });
        })();
    </script>
@endsection