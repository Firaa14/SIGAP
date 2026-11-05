@extends('layouts.app')

@section('title', 'Equipment CBM')

@section('page-title', 'Insert Equipment CBM')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Insert Equipment CBM</li>
@endsection

@section('content')
    <div class="card" style="max-width:760px;margin:32px auto;text-align:center;padding:56px 32px;">
        <div style="font-size:48px;margin-bottom:16px;" aria-hidden="true">⌛</div>
        <h2 style="margin:0 0 12px;color:var(--text-primary);">We Will See You Soon</h2>
        <p style="margin:0;color:var(--text-secondary);line-height:1.7;">
            Pengelolaan equipment CBM sedang dalam pengerjaan dan akan tersedia pada pembaruan berikutnya.
        </p>
    </div>
@endsection
