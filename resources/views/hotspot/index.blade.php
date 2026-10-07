@extends('layouts.admin')
@section('title', 'User Hotspot')

@php
    // Sort helper: generate URL with toggled direction
    function sortUrl($field, $currentSort, $currentDir) {
        $dir = ($currentSort === $field && $currentDir === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $field, 'dir' => $dir, 'page' => null]);
    }
    function sortIcon($field, $currentSort, $currentDir) {
        if ($currentSort !== $field) return '<i class="bi bi-chevron-expand sort-icon"></i>';
        return $currentDir === 'asc'
            ? '<i class="bi bi-sort-up sort-icon active"></i>'
            : '<i class="bi bi-sort-down sort-icon active"></i>';
    }
@endphp

@section('content')
{{-- Toolbar --}}
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <form class="d-flex gap-2" method="GET">
        <div class="input-group input-group-sm" style="min-width:240px">
            <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari username...">
        </div>
        @if(request('per_page'))<input type="hidden" name="per_page" value="{{ request('per_page') }}">@endif
        @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
        @if(request('dir'))<input type="hidden" name="dir" value="{{ request('dir') }}">@endif
    </form>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-action btn-soft-info" data-bs-toggle="modal" data-bs-target="#importRouterModal">
            <i class="bi bi-cloud-download"></i><span>Tarik Router</span>
        </button>
        <button class="btn btn-sm btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg"></i><span>Tambah</span>
        </button>
    </div>
</div>

{{-- Batch actions --}}
<div class="batch-bar mb-2">
    <input type="checkbox" class="form-check-input" id="selectAll" onchange="document.querySelectorAll('.rowCheck').forEach(c=>c.checked=this.checked)">
    <div class="batch-actions">
        <button type="button" class="btn-batch btn-batch-danger" onclick="doBatch('delete')"><i class="bi bi-trash3"></i> Hapus</button>
        <button type="button" class="btn-batch btn-batch-warn" onclick="doBatch('toggle')"><i class="bi bi-power"></i> Toggle</button>
        <button type="button" class="btn-batch btn-batch-ok" onclick="doBatch('sync')"><i class="bi bi-arrow-repeat"></i> Sinkron</button>
    </div>
</div>

<form id="batchForm" method="POST" action="{{ route('hotspot.batch') }}" style="display:none">@csrf
    <input type="hidden" name="batch_action" id="batchAction">
    <div id="batchIds"></div>
</form>

{{-- Table --}}
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th style="width:32px"></th>
            <th class="sortable"><a href="{{ sortUrl('username', $sort, $dir) }}">Username {!! sortIcon('username', $sort, $dir) !!}</a></th>
            <th class="sortable"><a href="{{ sortUrl('password', $sort, $dir) }}">Password {!! sortIcon('password', $sort, $dir) !!}</a></th>
            <th>Paket</th>
            <th>Anggota</th>
            <th>Tipe</th>
            <th class="sortable"><a href="{{ sortUrl('status', $sort, $dir) }}">Status {!! sortIcon('status', $sort, $dir) !!}</a></th>
            <th>Router</th>
            <th class="text-end">Aksi</th>
        </tr></thead>
        <tbody>
            @forelse($users as $h)
                <tr>
                    <td><input type="checkbox" value="{{ $h->id }}" class="form-check-input rowCheck"></td>
                    <td class="fw-semibold">{{ $h->username }}</td>
                    <td><code>{{ $h->password }}</code></td>
                    <td><small>{{ $h->package?->name ?: '-' }}</small></td>
                    <td><small>{{ $h->member?->name ?: '-' }}</small></td>
                    <td>
                        @if($h->member)
                            @if($h->member->type == 'guru')<span class="badge bg-success-subtle text-success">Guru</span>
                            @elseif($h->member->type == 'staff')<span class="badge bg-info-subtle text-info">Staff</span>
                            @else<span class="badge bg-primary-subtle text-primary">Siswa</span>@endif
                        @else <small class="text-muted">-</small> @endif
                    </td>
                    <td>
                        @if($h->status=='active')<span class="badge bg-success">Aktif</span>
                        @else<span class="badge bg-secondary">Nonaktif</span>@endif
                    </td>
                    <td>
                        @if($h->synced)<span class="badge bg-success-subtle text-success" title="Tersinkron {{ $h->synced_at?->diffForHumans() }}"><i class="bi bi-check-circle-fill"></i></span>
                        @else<span class="badge bg-warning-subtle text-warning">Belum</span>@endif
                    </td>
                    <td class="text-end">
                        <div class="action-group">
                            <form method="POST" action="{{ route('hotspot.sync', $h) }}" class="d-inline">@csrf
                                <button class="btn-icon btn-icon-ok" title="Sinkron"><i class="bi bi-arrow-repeat"></i></button>
                            </form>
                            <form method="POST" action="{{ route('hotspot.toggle', $h) }}" class="d-inline">@csrf
                                <button class="btn-icon btn-icon-warn" title="Toggle"><i class="bi bi-power"></i></button>
                            </form>
                            <form method="POST" action="{{ route('hotspot.destroy', $h) }}" class="d-inline" onsubmit="return confirm('Hapus user {{ $h->username }}?')">@csrf @method('DELETE')
                                <button class="btn-icon btn-icon-danger" title="Hapus"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Belum ada user hotspot.</td></tr>
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
                Membaca semua user hotspot di MikroTik dan <strong>otomatis membuat data anggota (Guru/Siswa)</strong> berdasarkan comment.
            </div>
            <ul class="small mb-3">
                <li>Comment mengandung <code>guru</code> → <strong>Data Guru</strong></li>
                <li>Comment mengandung <code>staff</code> / <code>tendik</code> → <strong>Data Guru</strong> (Staff)</li>
                <li>Lainnya → <strong>Data Siswa</strong></li>
                <li>User yang sudah ada di-skip</li>
            </ul>
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Pastikan <a href="{{ route('packages.index') }}">paket/profil</a> sudah diimpor dulu.
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

@push('head')
<style>
/* Sortable headers */
th.sortable a{ color:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; }
th.sortable a:hover{ color:var(--brand); }
.sort-icon{ font-size:.7rem; opacity:.35; transition:opacity .15s; }
.sort-icon.active{ opacity:1; color:var(--brand); }

/* Elegant action buttons */
.action-group{ display:flex; gap:2px; justify-content:flex-end; }
.btn-icon{
    width:32px; height:32px; border:none; border-radius:8px; background:transparent;
    display:inline-grid; place-items:center; font-size:.88rem; cursor:pointer;
    transition:background .12s, color .12s, transform .06s;
}
.btn-icon:active{ transform:scale(.9); }
.btn-icon-ok{ color:var(--online); }
.btn-icon-ok:hover{ background:var(--bs-success-bg-subtle); }
.btn-icon-warn{ color:var(--warn); }
.btn-icon-warn:hover{ background:var(--bs-warning-bg-subtle); }
.btn-icon-danger{ color:var(--danger); }
.btn-icon-danger:hover{ background:var(--bs-danger-bg-subtle); }

/* Batch bar */
.batch-bar{ display:flex; align-items:center; gap:10px; padding:6px 10px; background:var(--surface-2); border-radius:var(--r-sm); border:1px solid var(--line); }
.batch-actions{ display:flex; gap:4px; }
.btn-batch{
    border:none; background:transparent; padding:4px 10px; border-radius:6px;
    font-size:.78rem; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:4px;
    transition:background .12s, color .12s;
}
.btn-batch:active{ transform:scale(.95); }
.btn-batch-danger{ color:var(--danger); } .btn-batch-danger:hover{ background:var(--bs-danger-bg-subtle); }
.btn-batch-warn{ color:var(--warn); } .btn-batch-warn:hover{ background:var(--bs-warning-bg-subtle); }
.btn-batch-ok{ color:var(--online); } .btn-batch-ok:hover{ background:var(--bs-success-bg-subtle); }

/* Soft button style */
.btn-soft-info{
    background:var(--bs-info-bg-subtle); color:var(--bs-info-text-emphasis); border:1px solid var(--bs-info-border-subtle);
}
.btn-soft-info:hover{ background:var(--bs-info); color:#fff; border-color:var(--bs-info); }

/* Action btn with icon + text */
.btn-action{ display:inline-flex; align-items:center; gap:5px; font-weight:500; }
.btn-action i{ font-size:1rem; }
@media(max-width:575px){
    .btn-action span{ display:none; } /* icon only on small screens */
    .btn-action{ padding:.34rem .5rem; }
}
</style>
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
