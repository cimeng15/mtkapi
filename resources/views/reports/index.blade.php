@extends('layouts.admin')
@section('title', 'Laporan')

@section('content')
@php
    if(!function_exists('fmtBytes3')){ function fmtBytes3($b){ $u=['B','KB','MB','GB','TB']; $i=0; $b=(float)$b; while($b>=1024 && $i<4){$b/=1024;$i++;} return round($b,2).' '.$u[$i]; } }
@endphp

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Total Anggota</small><span class="h4">{{ $summary['total_members'] }}</span></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">User Hotspot Aktif</small><span class="h4">{{ $summary['hotspot_active'] }}</span></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Voucher Terpakai</small><span class="h4">{{ $summary['voucher_used'] }}/{{ $summary['voucher_total'] }}</span></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><small class="text-muted d-block">Pendapatan Voucher</small><span class="h4">Rp {{ number_format($summary['pendapatan_potensial'],0,',','.') }}</span></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-ticket-perforated me-2"></i>Rekap Voucher per Batch</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Batch</th><th>Total</th><th>Terpakai</th><th class="text-end">Nilai</th></tr></thead>
                    <tbody>
                        @forelse($batches as $b)
                            <tr><td><small>{{ $b->batch }}</small></td><td>{{ $b->total }}</td><td>{{ $b->used }}</td><td class="text-end">Rp {{ number_format($b->nilai,0,',','.') }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada voucher.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-graph-up me-2"></i>Pemakaian Data Tertinggi (dari Router)</div>
            @if($error)<div class="card-body"><div class="alert alert-warning mb-0 small">{{ $error }}</div></div>
            @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>User</th><th>Profil</th><th>Uptime</th><th class="text-end">Total Data</th></tr></thead>
                    <tbody>
                        @forelse($usage as $u)
                            <tr><td class="fw-semibold">{{ $u['name'] }}</td><td><small>{{ $u['profile'] }}</small></td><td><small>{{ $u['uptime'] }}</small></td><td class="text-end">{{ fmtBytes3($u['bytes_in']+$u['bytes_out']) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data pemakaian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
