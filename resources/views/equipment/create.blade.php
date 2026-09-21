@extends('layouts.app')

@section('title', 'Insert Equipment')

@section('page-title', 'Insert Equipment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Insert Equipment</li>
@endsection

@section('content')
<div class="card" style="max-width:760px;">
    <div class="card-header">
        <div class="card-title">
            <span class="card-title-icon">＋</span>
            Data Equipment
        </div>
    </div>

    <div class="card-body">
        @if (session('success'))
            <div style="margin-bottom:16px; padding:12px 14px; color:#065F46; background:#ECFDF5; border:1px solid #A7F3D0; border-radius:6px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="margin-bottom:16px; padding:12px 14px; color:#991B1B; background:#FEF2F2; border:1px solid #FECACA; border-radius:6px;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('equipment.store') }}">
            @csrf

            <div style="display:grid; gap:16px;">
                <div>
                    <label for="plta_id" class="form-label">PLTA</label>
                    <select name="plta_id" id="plta_id" class="form-control" required>
                        <option value="" data-i18n="equip_select_plta">Pilih PLTA</option>
                        @foreach ($pltas as $plta)
                            <option value="{{ $plta->id }}" @selected(old('plta_id') == $plta->id)>
                                {{ str_replace('PLTA ', '', $plta->nama_plta) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="unit" class="form-label">Unit</label>
                    <input type="text" name="unit" id="unit" class="form-control" value="{{ old('unit') }}" maxlength="50" required>
                </div>

                <div>
                    <label for="system" class="form-label">System</label>
                    <input type="text" name="system" id="system" class="form-control" value="{{ old('system') }}" maxlength="100" required>
                </div>

                <div>
                    <label for="equipment" class="form-label">Equipment</label>
                    <input type="text" name="equipment" id="equipment" class="form-control" value="{{ old('equipment') }}" maxlength="150" required>
                </div>

                <div>
                    <label for="kks" class="form-label">KKS</label>
                    <input type="text" name="kks" id="kks" class="form-control" value="{{ old('kks') }}" maxlength="100">
                </div>

                <div>
                    <label for="assetnum" class="form-label">ASSETNUM</label>
                    <input type="text" name="assetnum" id="assetnum" class="form-control" value="{{ old('assetnum') }}" maxlength="50" required>
                </div>
            </div>

            <div class="upload-actions" style="margin-top:20px;">
                <a href="{{ route('dashboard') }}" class="btn btn-danger btn-sm" id="btn-cancel-equipment" data-i18n="equip_cancel_btn">Batal</a>
                <button type="submit" class="btn btn-success btn-sm" id="btn-save-equipment" data-i18n="equip_save_btn">Simpan Equipment</button>
            </div>
        </form>
    </div>
</div>
@endsection
