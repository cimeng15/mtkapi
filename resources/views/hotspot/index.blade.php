@extends('layouts.admin')
@section('title', 'User Hotspot')

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <form class="d-flex gap-2" method="GET">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari username..." style="min-width:220px">
        @if(request('per_page'))<input type="hidden" name="per_page" value="{{ request('per_page') }}">@endif
        <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
    </form>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#importRouterModal"><i class="bi bi-cloud-download me-1"></i>Tarik dari Router</button>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Tambah Manual</button>
    </div>
</div>

<div class="d-flex gap-2 align-items-center mb-2">
    <div><input type="checkbox" id="selectAll" onchange="document.querySelectorAll('.rowCheck').forEach(c=>c.checked=this.checked)"></div>
    <button type="button" class="btn btn-sm btn-outline-danger" onclick="doBatch('delete')"><i class="bi bi-trash me-1"></i>Hapus</button>
    <button type="button" class="btn btn-sm btn-outline-warning" onclick="doBatch('toggle')"><i class="bi bi-power me-1"></i>Toggle</button>
    <button type="button" class="btn btn-sm btn-outline-success" onclick="doBatch('sync')"><i class="bi bi-arrow-repeat me-1"></i>Sinkron</button>
</div>

<form id="batchForm" method="POST" action="{{ route('hotspot.batch') }}" style="display:none">@csrf
    <input type="hidden" name="batch_action" id="batchAction">
    <div id="batchIds"></div>
</form>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th style="width:32px"></th>
            <th>Username</th><th>Password</th><th>Paket</th><th>Anggota</th><th>Tipe</th><th>Status</th><th>Router</th><th class="text-end">Aksi</th>
        </tr></thead>
        <tbody>
            @forelse($users as $h)
                <tr>
                    <td><input type="checkbox" value="{{ $h->id }}" class="rowCheck"></td>
                    <td class="fw-semibold">{{ $h->username }}</td>
                    <td><code>{{ $h->password }}</code></td>
                    <td>{{ $h->package?->name ?: '-' }}</td>
                    <td><small>{{ $h->member?->name ?: '-' }}</small></td>
                    <td>
                        @if($h->member)
                            @if($h->member->type == 'guru')<span class="badge bg-success-subtle text-success">Guru</span>
                            @elseif($h->member->type == 'staff')<span class="badge bg-info-subtle text-info">Staff</span>
                            @else<span class="badge bg-primary-subtle text-primary">Siswa</span>@endif
                        @else
                            <small class="text-muted">-</small>
                        @endif
                    </td>
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
                <tr><td colspan="9" class="text-center text-muted py-4">Belum ada user hotspot. Buat dari menu <a href="{{ route('students.index') }}">Data Siswa</a> / <a href="{{ route('teachers.index') }}">Data Guru</a>, atau tambah manual, atau tarik dari router.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>

{{-- Pagination + Per Page --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
    <div class="d-flex align-items-center gap-2">
        <small class="text-muted">Tampilkan</small>
        <select class="form-select form-select-sm" style="width:auto" onchange="changePerPage(this.value)">
            @foreach([10, 20, 50, 100] as $pp)
                <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
            @endforeach
        </select>
        <small class="text-muted">dari {{ $users->total() }} data</small>
    </div>
    <div>{{ $users->links() }}</div>
</div>

@push('modals')
{{-- Modal Tambah Manual --}}
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

{{-- Modal Import dari Router --}}
<div class="modal fade" id="importRouterModal" tabindex="-1"><div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-cloud-download me-2"></i>Tarik User dari Router</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Fitur ini membaca semua user hotspot di MikroTik, mengimpor ke database, dan <strong>otomatis membuat data anggota (Guru/Siswa)</strong> berdasarkan comment.
            </div>
            <ul class="small mb-3">
                <li>Comment mengandung kata <code>guru</code> → masuk <strong>Data Guru</strong></li>
                <li>Comment mengandung kata <code>staff</code> / <code>tendik</code> → masuk <strong>Data Guru</strong> (tipe Staff)</li>
                <li>Comment lainnya atau tanpa comment → masuk <strong>Data Siswa</strong></li>
                <li>Nama anggota diambil dari comment (tanpa penanda tipe), misal <code>Siti Aminah (guru)</code> → nama: <strong>Siti Aminah</strong></li>
                <li>User yang <strong>sudah ada</strong> di database akan <strong>di-skip</strong></li>
                <li>User <code>default-trial</code> otomatis diabaikan</li>
            </ul>
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>Pastikan paket/profil sudah diimpor terlebih dahulu</strong> di menu <a href="{{ route('packages.index') }}">Paket / Profil</a> agar pencocokan profil berjalan benar.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <form method="POST" action="{{ route('hotspot.import-router') }}" class="d-inline">@csrf
                <button class="btn btn-info text-white"><i class="bi bi-cloud-download me-1"></i>Tarik Sekarang</button>
            </form>
        </div>
    </div>
</div></div>
@endpush

<script>
function doBatch(action){
    var checked = document.querySelectorAll('.rowCheck:checked');
    if(!checked.length) return alert('Pilih data terlebih dahulu.');
    if(action==='delete' && !confirm('Hapus '+checked.length+' data?')) return;
    document.getElementById('batchAction').value = action;
    var c = document.getElementById('batchIds');
    c.innerHTML = '';
    checked.forEach(function(b){ c.insertAdjacentHTML('beforeend','<input type="hidden" name="ids[]" value="'+b.value+'">'); });
    document.getElementById('batchForm').submit();
}

function changePerPage(val){
    var url = new URL(window.location);
    url.searchParams.set('per_page', val);
    url.searchParams.delete('page');
    window.location = url;
}
</script>
@endsection
