@extends('index')

@section('main-content')
@can('admin') <!-- Hanya admin yang bisa mengakses halaman ini -->
<h1 class="h3 mb-4 text-gray-800">{{ $title ?? 'User List' }}</h1>

<a href="{{ route('user.create') }}" class="btn btn-primary mb-3">New User</a>

@if (session('message'))
<div class="alert alert-success">
    {{ session('message') }}
</div>
@endif

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Foto Profil</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aktivitas</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
        @php
        // pakai profile_photo kalau ada, fallback ke photo kalau masih dipakai
        $photoPath = $user->profile_photo ?? $user->photo;
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td class="text-center">
                @if ($photoPath)
                <img src="{{ asset('storage/' . $photoPath) }}"
                    alt="User Photo"
                    class="img-thumbnail"
                    style="width: 60px; height: 60px; object-fit: cover;">
                @else
                <img src="{{ asset('img/undraw_profile.svg') }}"
                    alt="Default Photo"
                    class="img-thumbnail"
                    style="width: 60px; height: 60px; object-fit: cover;">
                @endif
            </td>
            <td>{{ $user->name }} {{ $user->last_name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ ucfirst($user->role) }}</td>
            <td>
                <a href="{{ route('user.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('user.destroy', $user->id) }}" method="post" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Are you sure?')">
                        Delete
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Pagination Links -->
{{ $users->links() }}
@else
<p>You do not have permission to view this page.</p>
@endcan
@endsection