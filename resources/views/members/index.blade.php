@extends('layouts.admin')
@section('title', $scope['label'])

@section('content')
<div class="alert alert-{{ $scope['color'] }}-subtle d-flex align-items-center gap-2 py-2 small border">
    <i class="bi bi-{{ $scope['icon'] }}"></i>
    <span>Halaman ini hanya menampilkan <strong>{{ $scope['label'] }}</strong>. Akun hotspot dibuat <strong>otomatis</strong> (username &amp; password = ID login); kecepatan mengikuti kolom <strong>Paket</strong>.</span>
</div>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <form class="d-flex gap-2" method="GET">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama / ID / {{ strtolower($scope['detail']) }}..." style="min-width:220px">
        @if($scope['scope'] === 'guru')
        <select name="type" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <option value="">Semua</option>
            <option value="guru" @selected(request('type')=='guru')>Guru</option>
            <option value="staff" @selected(request('type')=='staff')>Staff/Tendik</option>
        </select>
        @endif
        <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route($scope['route'].'.template') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download me-1"></i>Template</a>
        <a href="{{ route($scope['route'].'.import.form') }}" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Import</a>
        <a href="{{ route($scope['route'].'.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah {{ $scope['singular'] }}</a>
    </div>
</div>

<form method="POST" action="{{ route($scope['route'].'.batch') }}" id="batchForm">@csrf

<div class="d-flex gap-2 align-items-center mb-2">
    <div><input type="checkbox" id="selectAll" onchange="document.querySelectorAll('.rowCheck').forEach(c=>c.checked=this.checked)"></div>
    <button type="submit" name="batch_action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return batchConfirm(this)"><i class="bi bi-trash me-1"></i>Hapus</button>
    <button type="submit" name="batch_action" value="provision" class="btn btn-sm btn-outline-primary"><i class="bi bi-wifi me-1"></i>Buat Akun Hotspot</button>
</div>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:32px"></th>
                <th>ID Login</th><th>Nama</th>
                @if($scope['scope'] === 'guru')<th>Tipe</th>@endif
                <th>{{ $scope['detail'] }}</th><th>Paket</th><th>Akun Hotspot</th><th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $m)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $m->id }}" class="rowCheck"></td>
                    <td><code>{{ $m->member_id }}</code></td>
                    <td>{{ $m->name }}</td>
                    @if($scope['scope'] === 'guru')
                        <td><span class="badge bg-{{ $m->type=='staff'?'secondary':'success' }}">{{ ucfirst($m->type) }}</span></td>
                    @endif
                    <td><small>{{ $scope['scope']==='siswa' ? ($m->class ?: '-') : ($m->department ?: $m->class ?: '-') }}</small></td>
                    <td><small>{{ $m->package?->name ?: '—' }}</small></td>
                    <td>
                        @if($m->hotspotUser)
                            @if($m->hotspotUser->synced)
                                <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>Aktif &amp; tersinkron</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning"><i class="bi bi-clock me-1"></i>Aktif (belum ke router)</span>
                            @endif
                        @else
                            <span class="badge bg-light text-muted">Belum ada</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route($scope['route'].'.edit', $m) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route($scope['route'].'.destroy', $m) }}" class="d-inline" onsubmit="return confirm('Hapus {{ $m->name }} beserta akun hotspotnya?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $scope['scope']==='guru' ? 8 : 7 }}" class="text-center text-muted py-4">Belum ada data. Silakan <a href="{{ route($scope['route'].'.import.form') }}">import</a> atau tambah manual.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
</form>

<div class="mt-3">{{ $members->links() }}</div>

<script>
function batchConfirm(btn){
    var checked = document.querySelectorAll('.rowCheck:checked');
    if(!checked.length){ alert('Pilih data terlebih dahulu.'); return false; }
    if(btn.value==='delete') return confirm('Hapus '+checked.length+' data?');
    return true;
}
</script>
@endsection
