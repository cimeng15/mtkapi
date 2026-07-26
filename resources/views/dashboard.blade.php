@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@php
    function fmtBytes($b){ $u=['B','KB','MB','GB','TB']; $i=0; $b=(float)$b; while($b>=1024 && $i<4){$b/=1024;$i++;} return round($b,2).' '.$u[$i]; }
@endphp

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label"><i class="bi bi-people"></i> Total Anggota</div>
            <div class="stat-value">{{ number_format($stats['members'], 0, ',', '.') }}</div>
            <div class="stat-foot">siswa &amp; guru terdaftar</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label"><i class="bi bi-person-vcard"></i> User Hotspot</div>
            <div class="stat-value">{{ number_format($stats['hotspot_users'], 0, ',', '.') }}</div>
            <div class="stat-foot">akun akses aktif</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label"><i class="bi bi-ticket-perforated"></i> Voucher Tersedia</div>
            <div class="stat-value">{{ number_format($stats['vouchers_unused'], 0, ',', '.') }}</div>
            <div class="stat-foot">belum terpakai</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat is-live">
            <div class="stat-label"><span class="live-dot"></span> Sedang Online</div>
            <div class="stat-value">{{ number_format($activeCount, 0, ',', '.') }}</div>
            <div class="stat-foot">terhubung ke jaringan</div>
        </div>
    </div>
</div>

@if($error)
    <div class="alert alert-warning"><i class="bi bi-router me-2"></i>{{ $error }}
        @if(auth()->user()->isSuperadmin())<a href="{{ route('settings.mikrotik.edit') }}" class="alert-link">Atur router →</a>@endif
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-hdd-network me-2"></i>Status Router</div>
            <div class="card-body">
                @if($resource)
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">Router</td><td class="text-end fw-semibold">{{ $router?->name }} ({{ $router?->host }})</td></tr>
                        <tr><td class="text-muted">RouterOS</td><td class="text-end">{{ $resource['version'] ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Board</td><td class="text-end">{{ $resource['board-name'] ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Uptime</td><td class="text-end">{{ $resource['uptime'] ?? '-' }}</td></tr>
                        <tr><td class="text-muted">CPU Load</td><td class="text-end">{{ $resource['cpu-load'] ?? '0' }}%</td></tr>
                        <tr><td class="text-muted">Memori Bebas</td><td class="text-end">{{ fmtBytes($resource['free-memory'] ?? 0) }} / {{ fmtBytes($resource['total-memory'] ?? 0) }}</td></tr>
                    </table>
                @else
                    <p class="text-muted mb-0"><i class="bi bi-plug me-2"></i>Data router tidak tersedia.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-broadcast me-2"></i>Pengguna Online</span>
                <a href="{{ route('hotspot.monitor') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>User</th><th>IP</th><th>Uptime</th><th class="text-end">Data</th></tr></thead>
                    <tbody>
                        @forelse(array_slice($activeUsers, 0, 8) as $a)
                            <tr>
                                <td class="fw-semibold">{{ $a['user'] ?? '-' }}</td>
                                <td><small>{{ $a['address'] ?? '-' }}</small></td>
                                <td><small>{{ $a['uptime'] ?? '-' }}</small></td>
                                <td class="text-end"><small>{{ fmtBytes(($a['bytes-in'] ?? 0)+($a['bytes-out'] ?? 0)) }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pengguna online.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
