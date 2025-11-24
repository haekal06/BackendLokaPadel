@extends('index')

@section('main-content')
<h1 class="h3 mb-4 text-gray-800">Pemesanan List</h1>

<a href="{{ route('pemesanan.create') }}" class="btn btn-primary mb-3">New Pemesanan</a>

@if (session('message'))
<div class="alert alert-success">{{ session('message') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>User</th>
            <th>Lapangan</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Durasi</th>
            <th>Waktu Selesai</th>
            <th>Status Main</th>
            <th>Status Pembayaran</th>
            <th>Metode Pembayaran</th>
            <th>Aktivitas</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pemesanans as $pemesanan)
        <tr>
            <td>{{ $loop->iteration + ($pemesanans->currentPage()-1)*$pemesanans->perPage() }}</td>
            <td>{{ $pemesanan->user->name }}</td>
            <td>{{ $pemesanan->lapangan->nama }}</td>
            <td>{{ $pemesanan->tanggal }}</td>
            <td>{{ $pemesanan->waktu }}</td>
            <td>{{ $pemesanan->durasi }} jam</td>
            <td>{{ $pemesanan->waktu_selesai }}</td>
            <td>
                @php $sm = $pemesanan->status_main; @endphp
                <span class="badge 
                    {{ $sm === 'Mendatang' ? 'badge-info' : ($sm === 'Berlangsung' ? 'badge-primary' : 'badge-secondary') }}">
                    {{ $sm }}
                </span>
            </td>
            <td>
                @php $st = $pemesanan->status_pembayaran; @endphp
                <span class="badge 
                    {{ $st === 'Sukses' ? 'badge-success' : ($st === 'Pending' ? 'badge-warning' : 'badge-danger') }}">
                    {{ $st }}
                </span>
            </td>
            <td>{{ $pemesanan->metode_pembayaran }}</td>
            <td>
                <a href="{{ route('pemesanan.edit', $pemesanan->id) }}" class="btn btn-sm btn-primary">Edit</a>

                {{-- Tombol Batalkan (langsung set status ke Batal) --}}
                @if($pemesanan->status_pembayaran !== 'Batal')
                <form action="{{ route('pemesanan.update', $pemesanan->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('PUT')
                    {{-- field minimal agar lolos validasi update --}}
                    <input type="hidden" name="user_id" value="{{ $pemesanan->user_id }}">
                    <input type="hidden" name="lapangan_id" value="{{ $pemesanan->lapangan_id }}">
                    <input type="hidden" name="tanggal" value="{{ $pemesanan->tanggal }}">
                    <input type="hidden" name="waktu" value="{{ $pemesanan->waktu }}">
                    <input type="hidden" name="durasi" value="{{ $pemesanan->durasi }}">
                    <input type="hidden" name="metode_pembayaran" value="{{ $pemesanan->metode_pembayaran }}">
                    <input type="hidden" name="status_pembayaran" value="Batal">
                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Batalkan pesanan ini?')">
                        Batalkan
                    </button>
                </form>
                @endif

                <form action="{{ route('pemesanan.destroy', $pemesanan->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pesanan ini?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $pemesanans->links() }}
@endsection