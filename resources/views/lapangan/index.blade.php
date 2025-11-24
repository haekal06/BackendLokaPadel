@extends('index')

@section('main-content')
<h1 class="h3 mb-4 text-gray-800">Lapangan List</h1>

<a href="{{ route('lapangan.create') }}" class="btn btn-primary mb-3">New Lapangan</a>

@if (session('message'))
<div class="alert alert-success">
    {{ session('message') }}
</div>
@endif

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Foto</th>
            <th>Aktivitas</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lapangans as $lapangan)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $lapangan->nama }}</td>
            <td>
                {{-- Format harga jadi Rp 550.000 style --}}
                Rp {{ number_format($lapangan->harga, 0, ',', '.') }}
            </td>
            <td>
                @if ($lapangan->foto)
                {{--
                            Sekarang foto disimpan di storage/app/public/lapangan
                            Kolom "foto" berisi "lapangan/namafile.jpg"
                            Jadi URL-nya: /storage/lapangan/namafile.jpg
                        --}}
                <img
                    src="{{ asset('storage/' . $lapangan->foto) }}"
                    alt="Foto Lapangan"
                    width="100"
                    height="100"
                    style="object-fit: cover;">
                @else
                <span>No Image</span>
                @endif
            </td>
            <td>
                <a href="{{ route('lapangan.edit', $lapangan->id) }}" class="btn btn-sm btn-primary">
                    Edit
                </a>
                <form action="{{ route('lapangan.destroy', $lapangan->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
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

{{ $lapangans->links() }} <!-- Untuk pagination -->

@endsection