@php
    if (!function_exists('mSortUrl')) {
        function mSortUrl($field, $currentSort, $currentDir) {
            $dir = ($currentSort === $field && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $field, 'dir' => $dir, 'page' => null]);
        }
        function mSortIcon($field, $currentSort, $currentDir) {
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
            <th class="sortable"><a href="{{ mSortUrl('member_id', $sort, $dir) }}">ID Login {!! mSortIcon('member_id', $sort, $dir) !!}</a></th>
            <th class="sortable"><a href="{{ mSortUrl('name', $sort, $dir) }}">Nama {!! mSortIcon('name', $sort, $dir) !!}</a></th>
            @if($scope['scope'] === 'guru')
            <th class="sortable"><a href="{{ mSortUrl('type', $sort, $dir) }}">Tipe {!! mSortIcon('type', $sort, $dir) !!}</a></th>
            @endif
            <th class="sortable"><a href="{{ mSortUrl($scope['scope']==='siswa' ? 'class' : 'department', $sort, $dir) }}">{{ $scope['detail'] }} {!! mSortIcon($scope['scope']==='siswa' ? 'class' : 'department', $sort, $dir) !!}</a></th>
            <th>Paket</th>
            <th>Akun Hotspot</th>
            <th class="text-end">Aksi</th>
        </tr></thead>
        <tbody>
            @forelse($members as $m)
                <tr>
                    <td><input type="checkbox" value="{{ $m->id }}" class="form-check-input rowCheck"></td>
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
                                <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning"><i class="bi bi-clock me-1"></i>Belum ke router</span>
                            @endif
                        @else
                            <span class="badge bg-light text-muted">Belum ada</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="action-group">
                            <a href="{{ route($scope['route'].'.edit', $m) }}" class="btn-icon btn-icon-brand" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route($scope['route'].'.destroy', $m) }}" class="d-inline" onsubmit="return confirm('Hapus {{ $m->name }}?')">@csrf @method('DELETE')
                                <button class="btn-icon btn-icon-danger" title="Hapus"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $scope['scope']==='guru' ? 8 : 7 }}" class="text-center text-muted py-4">Tidak ada data ditemukan.</td></tr>
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
        <small class="text-muted">dari {{ $members->total() }} data</small>
    </div>
    <div>{{ $members->links() }}</div>
</div>
