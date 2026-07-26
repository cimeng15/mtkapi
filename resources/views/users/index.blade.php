@extends('layouts.admin')
@section('title', 'Kelola Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0 small">Akun admin/operator yang dapat masuk ke aplikasi.</p>
    <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Admin</a>
</div>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
            @foreach($users as $u)
                <tr>
                    <td class="fw-semibold">{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                        @if($u->isSuperadmin())<span class="badge bg-primary">Superadmin</span>
                        @else<span class="badge bg-secondary">Operator</span>@endif
                    </td>
                    <td>@if($u->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-danger">Nonaktif</span>@endif</td>
                    <td class="text-end">
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @if($u->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Hapus admin ini?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
