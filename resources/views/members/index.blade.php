@extends('layouts.admin')
@section('title', $scope['label'])

@section('content')
<div class="alert alert-{{ $scope['color'] }}-subtle d-flex align-items-center gap-2 py-2 small border">
    <i class="bi bi-{{ $scope['icon'] }}"></i>
    <span>Halaman ini hanya menampilkan <strong>{{ $scope['label'] }}</strong>. Akun hotspot dibuat <strong>otomatis</strong> (username &amp; password = ID login).</span>
</div>

{{-- Toolbar --}}
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <form class="d-flex gap-2" method="GET">
        <div class="input-group input-group-sm" style="min-width:240px">
            <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control live-search" placeholder="Cari nama / ID / {{ strtolower($scope['detail']) }}..." autocomplete="off">
            <span class="input-group-text bg-transparent live-search-spinner d-none"><span class="spinner-border spinner-border-sm" style="width:14px;height:14px;border-width:2px;color:var(--brand)"></span></span>
        </div>
        @if($scope['scope'] === 'guru')
        <select name="type" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <option value="">Semua</option>
            <option value="guru" @selected(request('type')=='guru')>Guru</option>
            <option value="staff" @selected(request('type')=='staff')>Staff/Tendik</option>
        </select>
        @endif
        @if(request('per_page'))<input type="hidden" name="per_page" value="{{ request('per_page') }}">@endif
        @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
        @if(request('dir'))<input type="hidden" name="dir" value="{{ request('dir') }}">@endif
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route($scope['route'].'.template') }}" class="btn btn-sm btn-action btn-light">
            <i class="bi bi-download"></i><span>Template</span>
        </a>
        <a href="{{ route($scope['route'].'.import.form') }}" class="btn btn-sm btn-action btn-soft-success">
            <i class="bi bi-file-earmark-arrow-up"></i><span>Import</span>
        </a>
        <a href="{{ route($scope['route'].'.create') }}" class="btn btn-sm btn-action btn-primary">
            <i class="bi bi-plus-lg"></i><span>Tambah</span>
        </a>
    </div>
</div>

{{-- Batch actions --}}
<div class="batch-bar mb-2">
    <input type="checkbox" class="form-check-input" id="selectAll" onchange="document.querySelectorAll('.rowCheck').forEach(c=>c.checked=this.checked)">
    <div class="batch-actions">
        <button type="button" class="btn-batch btn-batch-danger" onclick="doBatch('delete')"><i class="bi bi-trash3"></i> Hapus</button>
        <button type="button" class="btn-batch btn-batch-ok" onclick="doBatch('provision')"><i class="bi bi-wifi"></i> Buat Akun</button>
    </div>
</div>

<form id="batchForm" method="POST" action="{{ route($scope['route'].'.batch') }}" style="display:none">@csrf
    <input type="hidden" name="batch_action" id="batchAction">
    <div id="batchIds"></div>
</form>

{{-- AJAX-replaceable table area --}}
<div id="ajax-table">
    @include('members._table')
</div>

@push('head')
<style>
th.sortable a{ color:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; }
th.sortable a:hover{ color:var(--brand); }
.sort-icon{ font-size:.7rem; opacity:.35; transition:opacity .15s; }
.sort-icon.active{ opacity:1; color:var(--brand); }
.action-group{ display:flex; gap:2px; justify-content:flex-end; }
.btn-icon{
    width:32px; height:32px; border:none; border-radius:8px; background:transparent;
    display:inline-grid; place-items:center; font-size:.88rem; cursor:pointer;
    transition:background .12s, color .12s, transform .06s;
}
.btn-icon:active{ transform:scale(.9); }
.btn-icon-brand{ color:var(--brand); } .btn-icon-brand:hover{ background:var(--brand-tint); }
.btn-icon-danger{ color:var(--danger); } .btn-icon-danger:hover{ background:var(--bs-danger-bg-subtle); }
.batch-bar{ display:flex; align-items:center; gap:10px; padding:6px 10px; background:var(--surface-2); border-radius:var(--r-sm); border:1px solid var(--line); }
.batch-actions{ display:flex; gap:4px; }
.btn-batch{
    border:none; background:transparent; padding:4px 10px; border-radius:6px;
    font-size:.78rem; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:4px;
    transition:background .12s, color .12s;
}
.btn-batch:active{ transform:scale(.95); }
.btn-batch-danger{ color:var(--danger); } .btn-batch-danger:hover{ background:var(--bs-danger-bg-subtle); }
.btn-batch-ok{ color:var(--online); } .btn-batch-ok:hover{ background:var(--bs-success-bg-subtle); }
.btn-soft-success{ background:var(--bs-success-bg-subtle); color:var(--bs-success-text-emphasis); border:1px solid var(--bs-success-border-subtle); }
.btn-soft-success:hover{ background:var(--online); color:#fff; border-color:var(--online); }
.btn-action{ display:inline-flex; align-items:center; gap:5px; font-weight:500; }
.btn-action i{ font-size:1rem; }
@media(max-width:575px){ .btn-action span{ display:none; } .btn-action{ padding:.34rem .5rem; } }
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
