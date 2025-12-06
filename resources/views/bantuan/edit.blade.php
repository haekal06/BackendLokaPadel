@extends('index')

@section('main-content')
@can('admin')
<h1 class="h3 mb-4 text-gray-800">{{ $title ?? 'Detail & Edit Bantuan' }}</h1>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('bantuan.update', $bantuan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Pengguna</label>
                <input type="text"
                    class="form-control"
                    value="{{ $bantuan->user->name ?? '-' }}"
                    disabled>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="text"
                    class="form-control"
                    value="{{ $bantuan->user->email ?? '-' }}"
                    disabled>
            </div>

            <div class="form-group">
                <label>Deskripsi Keluhan</label>
                <textarea class="form-control"
                    rows="4"
                    disabled>{{ $bantuan->deskripsi }}</textarea>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="baru" {{ $bantuan->status == 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="diproses" {{ $bantuan->status == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="selesai" {{ $bantuan->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <div class="form-group">
                <label>Catatan Admin (opsional)</label>
                <textarea name="catatan_admin"
                    class="form-control"
                    rows="3"
                    placeholder="Tambahkan catatan atau respon admin di sini...">{{ old('catatan_admin', $bantuan->catatan_admin) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('bantuan.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@else
<p>You do not have permission to view this page.</p>
@endcan
@endsection