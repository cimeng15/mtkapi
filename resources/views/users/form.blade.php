@extends('layouts.admin')
@section('title', $user->exists ? 'Edit Admin' : 'Tambah Admin')

@section('content')
<div class="row justify-content-center"><div class="col-lg-7">
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
            <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
            <div class="col-md-6"><label class="form-label">Password {{ $user->exists ? '(kosongkan jika tidak diubah)' : '' }}</label><input type="password" name="password" class="form-control" {{ $user->exists ? '' : 'required' }}></div>
            <div class="col-md-6"><label class="form-label">Konfirmasi Password</label><input type="password" name="password_confirmation" class="form-control"></div>
            <div class="col-md-6">
                <label class="form-label">Peran <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    <option value="operator" @selected(old('role',$user->role)=='operator')>Operator</option>
                    <option value="superadmin" @selected(old('role',$user->role)=='superadmin')>Superadmin</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" @checked(old('is_active', $user->is_active ?? true))><label class="form-check-label" for="act">Aktif</label></div>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
</div></div>
</div></div>
@endsection
