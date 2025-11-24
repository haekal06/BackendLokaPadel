@extends('index')

@section('main-content')
<h1 class="h3 mb-4 text-gray-800">Edit Lapangan</h1>

<form action="{{ route('lapangan.update', $lapangan->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="nama">Nama Lapangan</label>
        <input
            type="text"
            class="form-control"
            id="nama"
            name="nama"
            value="{{ $lapangan->nama }}"
            required>
    </div>

    <div class="form-group">
        <label for="harga">Harga</label>
        <input
            type="number"
            class="form-control"
            id="harga"
            name="harga"
            value="{{ $lapangan->harga }}"
            required>
    </div>

    <div class="form-group">
        <label for="foto">Foto Lapangan</label>
        @if ($lapangan->foto)
        <div class="mb-2">
            {{-- Preview foto lama --}}
            <img
                src="{{ asset('storage/' . $lapangan->foto) }}"
                alt="Foto Lapangan"
                width="120"
                height="120"
                style="object-fit: cover;">
        </div>
        @endif
        <input
            type="file"
            class="form-control"
            id="foto"
            name="foto"
            accept="image/*">
        <small class="form-text text-muted">
            Kosongkan jika tidak ingin mengubah foto.
        </small>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
</form>
@endsection