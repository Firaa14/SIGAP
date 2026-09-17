@extends('layouts.app')

@section('title', 'Status Equipment')

@section('page-title', 'Status Equipment')

@section('breadcrumb')
    <li class="breadcrumb-item">SIGAP</li>
    <li class="breadcrumb-item">Status Equipment</li>
@endsection

@push('styles')
    <style>
        .status-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .status-tab {
            display: inline-block;
            padding: 9px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid var(--border-color, #ddd);
            color: var(--text-muted);
        }

        .status-tab:hover {
            text-decoration: none;
        }

        .status-tab.normal.active {
            background: #22c55e;
            color: white;
            border-color: #22c55e;
        }

        .status-tab.abnormal.active {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }
    </style>
@endpush

@section('content')

    <div class="status-tabs">

        <a href="{{ route('status.index', ['status' => 'normal']) }}"
            class="status-tab normal {{ $status === 'normal' ? 'active' : '' }}">
            Normal
        </a>

        <a href="{{ route('status.index', ['status' => 'abnormal']) }}"
            class="status-tab abnormal {{ $status === 'abnormal' ? 'active' : '' }}">
            Abnormal
        </a>

    </div>


    <div class="card">

        <div class="card-header">

            <div class="card-title">

                <span class="card-title-icon">
                    ⚙
                </span>

                @if($status === 'normal')
                    Equipment Normal
                @else
                    Equipment Abnormal
                @endif

            </div>

        </div>


        <div class="table-wrapper">

            @if($data->count() > 0)

                <table class="activity-table">

                    <thead>
                        <tr>
                            <th>Waktu Upload</th>
                            <th>PLTA</th>
                            <th>ASSETNUM</th>
                            <th>Status</th>
                            <th>No WO</th>
                            <th>Report Date</th>
                            <th>Durasi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($data as $item)

                            @php
                                $statusClass = $item['status'] === 'Normal'
                                    ? 'normal'
                                    : 'abnormal';
                            @endphp

                            <tr>

                                <td>
                                    <span class="activity-time">
                                        {{ $item['time'] }}
                                    </span>
                                </td>

                                <td style="font-size:12.5px; font-weight:500;">
                                    {{ $item['plta'] }}
                                </td>

                                <td>
                                    <span class="activity-assetnum">
                                        {{ $item['assetnum'] }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $item['status'] }}
                                    </span>
                                </td>

                                <td class="activity-wo">
                                    {{ $item['wo'] }}
                                </td>

                                <td style="font-size:12.5px;">
                                    {{ $item['report_date'] }}
                                </td>

                                <td style="font-size:12.5px;">
                                    {{ $item['durasi_hari'] }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            @else

                <div class="empty-state" style="padding:32px 0;">

                    <div class="empty-state-icon">
                        ⚙
                    </div>

                    <div class="empty-state-title">
                        Tidak ada data
                    </div>

                    <div class="empty-state-text">
                        Belum ada equipment dengan status
                        {{ $status === 'normal' ? 'Normal' : 'Abnormal' }}.
                    </div>

                </div>

            @endif

        </div>

    </div>

@endsection