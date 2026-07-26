@extends('layouts.admin')
@section('title', 'Voucher Hotspot')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h6 class="mb-3"><i class="bi bi-magic me-2"></i>Generate Voucher</h6>
            <form method="POST" action="{{ route('vouchers.generate') }}">@csrf
                <div class="mb-2">
                    <label class="form-label small">Paket</label>
                    <select name="package_id" class="form-select form-select-sm" required>
                        <option value="">— Pilih Paket —</option>
                        @foreach($packages as $p)<option value="{{ $p->id }}">{{ $p->name }} (Rp {{ number_format($p->price,0,',','.') }})</option>@endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6"><label class="form-label small">Jumlah</label><input type="number" name="jumlah" class="form-control form-control-sm" value="10" min="1" max="500" required></div>
                    <div class="col-6"><label class="form-label small">Panjang Kode</label><input type="number" name="panjang_kode" class="form-control form-control-sm" value="6" min="4" max="12" required></div>
                </div>
                <div class="mb-2 mt-2"><label class="form-label small">Prefix (opsional)</label><input type="text" name="prefix" class="form-control form-control-sm" maxlength="10" placeholder="mis: SMK"></div>
                <div class="mb-2">
                    <label class="form-label small">Mode Password</label>
                    <select name="mode" class="form-select form-select-sm">
                        <option value="sama">Sama dengan username</option>
                        <option value="beda">Berbeda (acak)</option>
                    </select>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="push_router" value="1" id="push" checked>
                    <label class="form-check-label small" for="push">Langsung kirim ke MikroTik</label>
                </div>
                <button class="btn btn-primary btn-sm w-100"><i class="bi bi-lightning-charge me-1"></i>Generate</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-2">
            <form class="d-flex gap-2" method="GET">
                <select name="batch" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    <option value="">Semua Batch</option>
                    @foreach($batches as $b)<option value="{{ $b }}" @selected(request('batch')==$b)>{{ $b }}</option>@endforeach
                </select>
                <select name="status" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="unused" @selected(request('status')=='unused')>Belum dipakai</option>
                    <option value="used" @selected(request('status')=='used')>Terpakai</option>
                </select>
            </form>
            @if(request('batch'))
            <div class="d-flex gap-2">
                <a href="{{ route('vouchers.print', ['batch'=>request('batch')]) }}" target="_blank" class="btn btn-sm btn-dark"><i class="bi bi-printer me-1"></i>Cetak Batch</a>
                <form method="POST" action="{{ route('vouchers.destroy-batch') }}" onsubmit="return confirm('Hapus seluruh batch ini?')">@csrf @method('DELETE')
                    <input type="hidden" name="batch" value="{{ request('batch') }}">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus Batch</button>
                </form>
            </div>
            @endif
        </div>

        <div class="card"><div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light"><tr><th>Username</th><th>Password</th><th>Paket</th><th>Harga</th><th>Batch</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($vouchers as $v)
                        <tr>
                            <td class="fw-semibold"><code>{{ $v->username }}</code></td>
                            <td><code>{{ $v->password }}</code></td>
                            <td><small>{{ $v->package?->name ?: '-' }}</small></td>
                            <td><small>Rp {{ number_format($v->price,0,',','.') }}</small></td>
                            <td><small class="text-muted">{{ $v->batch }}</small></td>
                            <td>
                                @if($v->status=='used')<span class="badge bg-secondary">Terpakai</span>
                                @else<span class="badge bg-success">Tersedia</span>@endif
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('vouchers.destroy', $v) }}" onsubmit="return confirm('Hapus voucher?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-x"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada voucher.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
        <div class="mt-3">{{ $vouchers->links() }}</div>
    </div>
</div>
@endsection
