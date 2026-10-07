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

        {{-- Hapus Akun --}}
        <div class="card border-danger">
            <div class="card-header text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Akun</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Setelah akun dihapus, semua data akan hilang permanen.</p>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal"><i class="bi bi-trash me-1"></i>Hapus Akun</button>
            </div>
        </div>

    </div>
</div>

{{-- Modal Hapus Akun --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content">
            @csrf @method('delete')
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Akun</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Yakin ingin menghapus akun? Semua data akan hilang permanen.</p>
                <div class="mb-0">
                    <label for="delete_password" class="form-label">Masukkan password untuk konfirmasi</label>
                    <input type="password" id="delete_password" name="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="Password" required>
                    @error('password', 'userDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-danger"><i class="bi bi-trash me-1"></i>Hapus Permanen</button>
            </div>
        </form>
    </div>
</div>
@endsection
