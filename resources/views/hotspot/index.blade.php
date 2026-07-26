@extends('layouts.admin')
@section('title', 'User Hotspot')

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <form class="d-flex gap-2" method="GET">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari username..." style="min-width:220px">
        <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
    </form>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Tambah Manual</button>
</div>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th>Username</th><th>Password</th><th>Paket</th><th>Anggota</th><th>Status</th><th>Router</th><th class="text-end">Aksi</th>
        </tr></thead>
        <tbody>
            @forelse($users as $h)
                <tr>
                    <td class="fw-semibold">{{ $h->username }}</td>
                    <td><code>{{ $h->password }}</code></td>
                    <td>{{ $h->package?->name ?: '-' }}</td>
                    <td><small>{{ $h->member?->name ?: '-' }}</small></td>
                    <td>
                        @if($h->status=='active')<span class="badge bg-success">Aktif</span>
                        @else<span class="badge bg-secondary">Nonaktif</span>@endif
                    </td>
                    <td>
                        @if($h->synced)<span class="badge bg-success-subtle text-success" title="Tersinkron {{ $h->synced_at?->diffForHumans() }}"><i class="bi bi-check-circle"></i></span>
                        @else<span class="badge bg-warning-subtle text-warning">Belum</span>@endif
                    </td>
                    <td class="text-end text-nowrap">
                        <form method="POST" action="{{ route('hotspot.sync', $h) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-success" title="Sinkron ke router"><i class="bi bi-arrow-repeat"></i></button>
                        </form>
                        <form method="POST" action="{{ route('hotspot.toggle', $h) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-warning" title="Aktif/Nonaktif"><i class="bi bi-power"></i></button>
                        </form>
                        <form method="POST" action="{{ route('hotspot.destroy', $h) }}" class="d-inline" onsubmit="return confirm('Hapus user {{ $h->username }}?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada user hotspot. Buat dari menu <a href="{{ route('students.index') }}">Data Siswa</a> / <a href="{{ route('teachers.index') }}">Data Guru</a>, atau tambah manual.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $users->links() }}</div>

<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog">
    <form method="POST" action="{{ route('hotspot.store') }}" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title">Tambah User Hotspot</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Username <span class="text-danger">*</span></label><input name="username" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Password <span class="text-danger">*</span></label><input name="password" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Paket <span class="text-danger">*</span></label>
                <select name="package_id" class="form-select" required>
                    <option value="">— Pilih —</option>
                    @foreach($packages as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                </select>
            </div>
            <div class="mb-0"><label class="form-label">Keterangan</label><input name="comment" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
    </form>
</div></div>
@endsection
