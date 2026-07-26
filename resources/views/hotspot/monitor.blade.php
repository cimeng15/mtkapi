@extends('layouts.admin')
@section('title', 'Monitoring Sesi Aktif')

@section('content')
@php
    if(!function_exists('fmtBytes2')){ function fmtBytes2($b){ $u=['B','KB','MB','GB','TB']; $i=0; $b=(float)$b; while($b>=1024 && $i<4){$b/=1024;$i++;} return round($b,2).' '.$u[$i]; } }
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div><span class="badge bg-info fs-6"><i class="bi bi-broadcast me-1"></i>{{ count($active) }} pengguna online</span></div>
    <div class="d-flex align-items-center gap-2">
        <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="autoRefresh" checked><label class="form-check-label small" for="autoRefresh">Auto-refresh 15 dtk</label></div>
        <a href="{{ route('hotspot.monitor') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-clockwise"></i></a>
    </div>
</div>

@if($error)
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>{{ $error }}</div>
@endif

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th>User</th><th>IP Address</th><th>MAC</th><th>Uptime</th><th>Download</th><th>Upload</th><th class="text-end">Aksi</th>
        </tr></thead>
        <tbody>
            @forelse($active as $a)
                <tr>
                    <td class="fw-semibold"><i class="bi bi-person-fill text-success me-1"></i>{{ $a['user'] ?? '-' }}</td>
                    <td><code>{{ $a['address'] ?? '-' }}</code></td>
                    <td><small class="text-muted">{{ $a['mac-address'] ?? '-' }}</small></td>
                    <td>{{ $a['uptime'] ?? '-' }}</td>
                    <td>{{ fmtBytes2($a['bytes-out'] ?? 0) }}</td>
                    <td>{{ fmtBytes2($a['bytes-in'] ?? 0) }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('hotspot.disconnect') }}" class="d-inline" onsubmit="return confirm('Putuskan sesi {{ $a['user'] ?? '' }}?')">@csrf
                            <input type="hidden" name="active_id" value="{{ $a['.id'] ?? '' }}">
                            <input type="hidden" name="username" value="{{ $a['user'] ?? '' }}">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Putus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pengguna yang sedang online.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@endsection

@push('scripts')
<script>
    let t = setTimeout(reload, 15000);
    function reload(){ if(document.getElementById('autoRefresh').checked) location.reload(); }
    document.getElementById('autoRefresh').addEventListener('change', function(){
        clearTimeout(t); if(this.checked) t = setTimeout(reload, 15000);
    });
</script>
@endpush
