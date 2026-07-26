@extends('layouts.admin')
@section('title', 'Paket / Profil Hotspot')

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0 small">Paket menentukan kecepatan, durasi & harga. Setiap paket memetakan ke satu <em>user-profile</em> di MikroTik.</p>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('packages.import-router') }}" onsubmit="return confirm('Tarik profil yang sudah ada di MikroTik menjadi paket?')">@csrf
            <button class="btn btn-sm btn-outline-info"><i class="bi bi-cloud-download me-1"></i>Tarik dari MikroTik</button>
        </form>
        <form method="POST" action="{{ route('packages.sync-all') }}" onsubmit="return confirm('Kirim semua paket aktif ke MikroTik?')">@csrf
            <button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-repeat me-1"></i>Sinkron Semua</button>
        </form>
        <a href="{{ route('packages.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Paket</a>
    </div>
</div>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th>Nama Paket</th><th>Profil Router</th><th>Kecepatan</th><th>Durasi</th><th>Shared</th><th>Harga</th><th>Untuk</th><th class="text-end">Aksi</th>
        </tr></thead>
        <tbody>
            @forelse($packages as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->name }} @unless($p->is_active)<span class="badge bg-secondary">nonaktif</span>@endunless</td>
                    <td><code>{{ $p->mikrotik_profile }}</code></td>
                    <td>{{ $p->rate_limit ?: '-' }}</td>
                    <td>{{ $p->session_timeout ?: 'Unlimited' }}</td>
                    <td>{{ $p->shared_users }}</td>
                    <td>Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                    <td><span class="badge bg-light text-dark">{{ ucfirst($p->for_type) }}</span></td>
                    <td class="text-end text-nowrap">
                        <form method="POST" action="{{ route('packages.sync', $p) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-success" title="Kirim profil ke MikroTik"><i class="bi bi-arrow-repeat"></i></button>
                        </form>
                        <a href="{{ route('packages.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('packages.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Hapus paket ini?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada paket.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $packages->links() }}</div>
@endsection
