@extends('layouts.admin')
@section('title', 'Profil Saya')

@section('content')
<div class="row justify-content-center g-4">
    <div class="col-lg-8">

        {{-- Info Profil --}}
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-person me-2"></i>Informasi Profil</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Perbarui nama dan email akun Anda.</p>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('patch')
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                    @if (session('status') === 'profile-updated')
                        <span class="text-success ms-2 small"><i class="bi bi-check-circle me-1"></i>Tersimpan.</span>
                    @endif
                </form>
            </div>
        </div>

        {{-- Ubah Password --}}
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-key me-2"></i>Ubah Password</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Gunakan password yang kuat dan unik.</p>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('put')
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" id="current_password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                        @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" id="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                        @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Ubah Password</button>
                    @if (session('status') === 'password-updated')
                        <span class="text-success ms-2 small"><i class="bi bi-check-circle me-1"></i>Password diubah.</span>
                    @endif
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
