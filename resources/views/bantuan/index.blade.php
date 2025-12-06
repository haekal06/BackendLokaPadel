@extends('index')

@section('main-content')
@can('admin')
<h1 class="h3 mb-4 text-gray-800">{{ $title ?? 'Daftar Bantuan' }}</h1>

@if (session('message'))
<div class="alert alert-success">
    {{ session('message') }}
</div>
@endif

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Pengguna</th>
            <th>Email</th>
            <th>Deskripsi</th>
            <th>Status</th>
            <th>Dibuat</th>
            <th>Aktivitas</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($bantuans as $bantuan)
        <tr>
            <td>{{ $loop->iteration + ($bantuans->currentPage() - 1) * $bantuans->perPage() }}</td>
            <td>{{ $bantuan->user->name ?? '-' }}</td>
            <td>{{ $bantuan->user->email ?? '-' }}</td>
            <td>
                {{ \Illuminate\Support\Str::limit($bantuan->deskripsi, 60) }}
            </td>
            <td>
                @php
                $badgeClass = 'secondary';
                if ($bantuan->status === 'baru') $badgeClass = 'warning';
                if ($bantuan->status === 'diproses') $badgeClass = 'info';
                if ($bantuan->status === 'selesai') $badgeClass = 'success';
                @endphp
                <span class="badge badge-{{ $badgeClass }}">
                    {{ ucfirst($bantuan->status) }}
                </span>
            </td>
            <td>{{ $bantuan->created_at->format('d M Y H:i') }}</td>
            <td>
                <a href="{{ route('bantuan.edit', $bantuan->id) }}" class="btn btn-sm btn-primary">
                    Detail / Edit
                </a>
                <form action="{{ route('bantuan.destroy', $bantuan->id) }}"
                    method="post"
                    style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Yakin ingin menghapus data bantuan ini?')">
                        Delete
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">Belum ada data bantuan.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $bantuans->links() }}
@else
<p>You do not have permission to view this page.</p>
@endcan
@endsection