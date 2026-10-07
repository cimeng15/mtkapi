@php
    if (!function_exists('hSortUrl')) {
        function hSortUrl($field, $currentSort, $currentDir) {
            $dir = ($currentSort === $field && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $field, 'dir' => $dir, 'page' => null]);
        }
        function hSortIcon($field, $currentSort, $currentDir) {
            if ($currentSort !== $field) return '<i class="bi bi-chevron-expand sort-icon"></i>';
            return $currentDir === 'asc'
                ? '<i class="bi bi-sort-up sort-icon active"></i>'
                : '<i class="bi bi-sort-down sort-icon active"></i>';
        }
    }
@endphp

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th style="width:32px"></th>
            <th class="sortable"><a href="{{ hSortUrl('username', $sort, $dir) }}">Username {!! hSortIcon('username', $sort, $dir) !!}</a></th>
            <th class="sortable"><a href="{{ hSortUrl('password', $sort, $dir) }}">Password {!! hSortIcon('password', $sort, $dir) !!}</a></th>
            <th>Paket</th>
            <th>Anggota</th>
            <th>Tipe</th>
            <th class="sortable"><a href="{{ hSortUrl('status', $sort, $dir) }}">Status {!! hSortIcon('status', $sort, $dir) !!}</a></th>
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
                <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>

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
