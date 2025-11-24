@extends('index')

@section('main-content')
<h1 class="h3 mb-4 text-gray-800">Create New Lapangan</h1>

<form action="{{ route('lapangan.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label for="nama">Nama Lapangan</label>
        <input
            type="text"
            class="form-control"
            id="nama"
            name="nama"
            required>
    </div>

    <div class="form-group">
        <label for="harga">Harga</label>
        <input
            type="number"
            class="form-control"
            id="harga"
            name="harga"
            required>
    </div>

    <div class="form-group">
        <label for="foto">Foto Lapangan</label>
        <input
            type="file"
            class="form-control"
            id="foto"
            name="foto"
            accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary">Create</button>
</form>
@endsection