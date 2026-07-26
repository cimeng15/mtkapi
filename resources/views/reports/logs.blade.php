@extends('layouts.admin')
@section('title', 'Log Aktivitas')

@section('content')
<div class="card"><div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Waktu</th><th>Admin</th><th>Aksi</th><th>Keterangan</th><th>IP</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td><small>{{ $log->created_at->format('d/m/Y H:i') }}</small></td>
                    <td>{{ $log->user?->name ?: 'Sistem' }}</td>
                    <td><span class="badge bg-light text-dark">{{ $log->action }}</span></td>
                    <td><small>{{ $log->description }}</small></td>
                    <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada aktivitas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
