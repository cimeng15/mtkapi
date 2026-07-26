@extends('layouts.admin')
@section('title', $package->exists ? 'Edit Paket' : 'Tambah Paket')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $package->exists ? route('packages.update', $package) : route('packages.store') }}">
        @csrf
        @if($package->exists) @method('PUT') @endif

        {{-- ============ BAGIAN 1: DISINKRON KE MIKROTIK ============ --}}
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-success"><i class="bi bi-router me-1"></i>Dikirim ke MikroTik</span>
            <small class="text-muted">Field ini membentuk <em>user-profile</em> hotspot di router.</small>
        </div>
        <div class="row g-3 p-3 mb-4 rounded border bg-success-subtle bg-opacity-10" style="--bs-bg-opacity:.15;">
            <div class="col-md-6">
                <label class="form-label">Nama Profil di MikroTik <span class="text-danger">*</span></label>
                <input type="text" name="mikrotik_profile" class="form-control" value="{{ old('mikrotik_profile', $package->mikrotik_profile) }}" required placeholder="mis: siswa-3jam">
                <small class="text-muted">Nama <em>user-profile</em> hotspot yang dibuat/diperbarui di router.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Shared Users <span class="text-danger">*</span></label>
                <input type="number" name="shared_users" class="form-control" value="{{ old('shared_users', $package->shared_users ?? 1) }}" min="1" required>
                <small class="text-muted">Jumlah perangkat yang boleh online bersamaan per akun.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Rate Limit (down/up)</label>
                <input type="text" name="rate_limit" class="form-control" value="{{ old('rate_limit', $package->rate_limit) }}" placeholder="mis: 2M/1M">
            </div>
            <div class="col-md-4">
                <label class="form-label">Durasi Sesi</label>
                <input type="text" name="session_timeout" class="form-control" value="{{ old('session_timeout', $package->session_timeout) }}" placeholder="mis: 3h, 1d (kosong=unlimited)">
            </div>
            <div class="col-md-4">
                <label class="form-label">Kuota Data</label>
                <input type="text" name="data_limit" class="form-control" value="{{ old('data_limit', $package->data_limit) }}" placeholder="mis: 500M, 2G (kosong=unlimited)">
            </div>
        </div>

        {{-- ============ BAGIAN 2: HANYA DI APLIKASI ============ --}}
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-secondary"><i class="bi bi-database me-1"></i>Data Aplikasi</span>
            <small class="text-muted">Untuk laporan & tampilan — tidak dikirim ke router.</small>
        </div>
        <div class="row g-3 p-3 rounded border bg-light">
            <div class="col-md-6">
                <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $package->name) }}" required placeholder="mis: Siswa 3 Jam">
                <small class="text-muted">Label ramah untuk ditampilkan di aplikasi.</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                <input type="number" name="price" class="form-control" value="{{ old('price', $package->price ?? 0) }}" min="0" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Untuk <span class="text-danger">*</span></label>
                <select name="for_type" class="form-select" required>
                    @foreach(['umum'=>'Umum','guru'=>'Guru','siswa'=>'Siswa','staff'=>'Staff'] as $v=>$l)
                        <option value="{{ $v }}" @selected(old('for_type',$package->for_type)==$v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Keterangan</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $package->description) }}</textarea>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" @checked(old('is_active', $package->is_active ?? true))>
                    <label class="form-check-label" for="act">Paket Aktif</label>
                </div>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
            <a href="{{ route('packages.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
</div></div>
@endsection
