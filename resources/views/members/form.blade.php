@extends('layouts.admin')
@section('title', ($member->exists ? 'Edit ' : 'Tambah ').$scope['singular'])

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $member->exists ? route($scope['route'].'.update', $member) : route($scope['route'].'.store') }}">
        @csrf
        @if($member->exists) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ID Login (NIS/NISN/NIP) <span class="text-danger">*</span></label>
                <input type="text" name="member_id" class="form-control" value="{{ old('member_id', $member->member_id) }}" required>
                <small class="text-muted">Dipakai sebagai username & password hotspot.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $member->name) }}" required>
            </div>

            @if($scope['scope'] === 'guru')
                <div class="col-md-6">
                    <label class="form-label">Tipe <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="guru" @selected(old('type',$member->type)=='guru')>Guru</option>
                        <option value="staff" @selected(old('type',$member->type)=='staff')>Staff / Tendik</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jurusan / Bagian</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $member->department) }}" placeholder="mis: Matematika / Tata Usaha">
                </div>
            @else
                <input type="hidden" name="type" value="siswa">
                <div class="col-md-6">
                    <label class="form-label">Kelas / Rombel</label>
                    <input type="text" name="class" class="form-control" value="{{ old('class', $member->class) }}" placeholder="mis: X IPA 1">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jurusan (opsional)</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $member->department) }}" placeholder="mis: IPA / TKJ">
                </div>
            @endif

            <div class="col-md-6">
                <label class="form-label">Paket Kecepatan</label>
                <select name="package_id" class="form-select">
                    <option value="">— Tanpa paket (profil default router) —</option>
                    @foreach($packages as $p)
                        <option value="{{ $p->id }}" @selected(old('package_id', $member->package_id)==$p->id)>{{ $p->name }} @if($p->rate_limit)({{ $p->rate_limit }})@endif</option>
                    @endforeach
                </select>
                <small class="text-muted">Menentukan kecepatan akun hotspot yang dibuat otomatis.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $member->phone) }}">
            </div>

            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" @checked(old('is_active', $member->is_active ?? true))>
                    <label class="form-check-label" for="act">Aktif</label>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
            <a href="{{ route($scope['route'].'.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
</div></div>
@endsection
