@extends('layouts.admin')
@section('title', 'Import '.$scope['label'])

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <h5 class="mb-3"><i class="bi bi-file-earmark-excel text-success me-2"></i>Import {{ $scope['label'] }} (Excel / CSV)</h5>

            <div class="alert alert-{{ $scope['color'] }}-subtle small border">
                <i class="bi bi-{{ $scope['icon'] }} me-1"></i>
                @if($scope['scope']==='siswa')
                    Semua baris pada file ini akan disimpan sebagai <strong>Siswa</strong>.
                @else
                    Baris akan disimpan sebagai <strong>Guru</strong>. Untuk staff/tendik, isi kolom <code>type</code> dengan <code>staff</code>.
                @endif
            </div>

            <form method="POST" action="{{ route($scope['route'].'.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Pilih file (.xlsx, .xls, .csv) — maks 5MB</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text text-muted small">Template CSV menggunakan pemisah titik koma (;) agar tampil rapi di Excel.</div>
                </div>
                <button class="btn btn-success"><i class="bi bi-upload me-1"></i>Import Sekarang</button>
                <a href="{{ route($scope['route'].'.template') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Unduh Template {{ $scope['singular'] }}</a>
                <a href="{{ route($scope['route'].'.index') }}" class="btn btn-light">Kembali</a>
            </form>

            <hr class="my-4">
            <h6>Format Kolom</h6>
            <p class="text-muted small">Baris pertama file harus berisi nama kolom (header). Kolom yang dikenali:</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered small">
                    <thead class="table-light"><tr><th>Kolom</th><th>Wajib?</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        <tr><td><code>member_id</code> / <code>nis</code> / <code>nisn</code> / <code>nip</code></td><td>Ya</td><td>ID login (username & password hotspot)</td></tr>
                        <tr><td><code>name</code> / <code>nama</code></td><td>Ya</td><td>Nama lengkap</td></tr>
                        <tr class="table-warning"><td><code>package</code> / <code>paket</code> / <code>profile</code></td><td>Disarankan</td><td><strong>Paket kecepatan</strong> — tulis nama paket persis (lihat daftar di bawah)</td></tr>
                        @if($scope['scope']==='guru')
                        <tr><td><code>type</code> / <code>tipe</code></td><td>Tidak</td><td>Kosong = guru; isi <code>staff</code> untuk tendik</td></tr>
                        <tr><td><code>department</code> / <code>jurusan</code></td><td>Tidak</td><td>Jurusan / bagian</td></tr>
                        @else
                        <tr><td><code>class</code> / <code>kelas</code></td><td>Tidak</td><td>Kelas / rombel</td></tr>
                        @endif
                        <tr><td><code>phone</code> / <code>hp</code></td><td>Tidak</td><td>Nomor telepon</td></tr>
                    </tbody>
                </table>
            </div>

            <h6 class="mt-3">Nama Paket yang Tersedia</h6>
            <p class="text-muted small mb-2">Salin salah satu nama berikut ke kolom <code>package</code>:</p>
            <div class="mb-3">
                @forelse($packages as $p)
                    <span class="badge bg-primary-subtle text-primary border me-1 mb-1">{{ $p->name }}</span>
                @empty
                    <span class="text-danger small">Belum ada paket. Buat dulu di menu <a href="{{ route('packages.index') }}">Paket/Profil</a>.</span>
                @endforelse
            </div>

            <div class="alert alert-success small mb-2"><i class="bi bi-check-circle me-1"></i>Setelah import, <strong>akun hotspot langsung dibuat otomatis & aktif</strong> untuk tiap {{ strtolower($scope['singular']) }} (username & password = ID login).</div>
            <div class="alert alert-info small mb-0"><i class="bi bi-info-circle me-1"></i>Data dengan ID yang sama akan <strong>diperbarui</strong>, bukan digandakan. Kolom paket kosong/tidak dikenali → akun tetap dibuat memakai profil <em>default</em> router.</div>
        </div></div>
    </div>
</div>
@endsection
