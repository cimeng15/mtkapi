@extends('layouts.admin')
@section('title', 'Pengaturan Router MikroTik')

@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card"><div class="card-body">
        <h5 class="mb-1"><i class="bi bi-router text-primary me-2"></i>Koneksi RouterOS API</h5>
        <p class="text-muted small mb-4">Pastikan service <code>api</code> aktif di MikroTik: <code>/ip service enable api</code> (port default 8728).</p>

        <form method="POST" action="{{ route('settings.mikrotik.update') }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama Router</label><input name="name" class="form-control" value="{{ old('name', $setting->name ?: 'Router Utama') }}" required></div>
                <div class="col-md-6"><label class="form-label">Host / IP <span class="text-danger">*</span></label><input name="host" class="form-control" value="{{ old('host', $setting->host) }}" placeholder="192.168.88.1" required></div>
                <div class="col-md-4"><label class="form-label">Port API <span class="text-danger">*</span></label><input type="number" name="port" class="form-control" value="{{ old('port', $setting->port ?: 8728) }}" required></div>
                <div class="col-md-4"><label class="form-label">Username <span class="text-danger">*</span></label><input name="username" class="form-control" value="{{ old('username', $setting->username) }}" required></div>
                <div class="col-md-4"><label class="form-label">Password</label><input type="password" name="password" class="form-control" placeholder="{{ $setting->exists && $setting->password ? '•••••• (tersimpan)' : '' }}"><small class="text-muted">Kosongkan bila tak diubah.</small></div>
                <div class="col-md-8"><label class="form-label">Nama DNS Portal (untuk voucher)</label><input name="dns_name" class="form-control" value="{{ old('dns_name', $setting->dns_name) }}" placeholder="mis: hotspot.sekolah.id"></div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="use_ssl" value="1" id="ssl" @checked(old('use_ssl', $setting->use_ssl))><label class="form-check-label" for="ssl">Gunakan SSL (8729)</label></div>
                </div>
            </div>

            @if($setting->exists && $setting->last_connected_at)
                <p class="text-success small mt-3 mb-0"><i class="bi bi-check-circle me-1"></i>Terakhir terhubung: {{ $setting->last_connected_at->diffForHumans() }}</p>
            @endif

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
            </div>
        </form>

        @if($setting->exists)
        <hr class="my-4">
        <form method="POST" action="{{ route('settings.mikrotik.test') }}">
            @csrf
            <button class="btn btn-outline-success"><i class="bi bi-plug me-1"></i>Tes Koneksi</button>
            <small class="text-muted ms-2">Uji koneksi ke router dengan konfigurasi tersimpan.</small>
        </form>
        @endif
    </div></div>
</div></div>
@endsection
