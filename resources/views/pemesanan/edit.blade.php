@extends('index')

@section('main-content')
<h1 class="h3 mb-4 text-gray-800">Edit Pemesanan</h1>

<a href="{{ route('pemesanan.index') }}" class="btn btn-secondary mb-3">Back to List</a>

@if (session('message'))
<div class="alert alert-success">{{ session('message') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form action="{{ route('pemesanan.update', $pemesanan->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="user_id">User</label>
        <select name="user_id" id="user_id" class="form-control">
            @foreach ($users as $user)
            <option value="{{ $user->id }}" {{ $pemesanan->user_id == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="lapangan_id">Lapangan</label>
        <select name="lapangan_id" id="lapangan_id" class="form-control">
            @foreach ($lapangans as $lapangan)
            <option value="{{ $lapangan->id }}" data-harga="{{ $lapangan->harga }}" {{ $pemesanan->lapangan_id == $lapangan->id ? 'selected' : '' }}>
                {{ $lapangan->nama }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="tanggal">Tanggal</label>
        <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ $pemesanan->tanggal }}">
    </div>

    <div class="form-group">
        <label for="waktu">Waktu</label>
        <input type="time" name="waktu" id="waktu" class="form-control" value="{{ $pemesanan->waktu }}">
    </div>

    <div class="form-group">
        <label for="durasi">Durasi (jam)</label>
        <input type="number" name="durasi" id="durasi" class="form-control" value="{{ $pemesanan->durasi }}" required min="1">
    </div>

    <div class="form-group">
        <label for="total_harga">Total Harga</label>
        <input type="text" class="form-control" id="total_harga" name="total_harga" value="{{ $pemesanan->total_harga }}" readonly>
    </div>

    <div class="form-group">
        <label for="waktu_selesai">Waktu Selesai</label>
        <input type="text" class="form-control" id="waktu_selesai" name="waktu_selesai" value="{{ $pemesanan->waktu_selesai }}" readonly>
    </div>

    <div class="form-group">
        <label for="metode_pembayaran">Metode Pembayaran</label>
        <select class="form-control" id="metode_pembayaran" name="metode_pembayaran">
            <option value="Belum Bayar" {{ $pemesanan->metode_pembayaran === 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
            <option value="Transfer Bank" {{ $pemesanan->metode_pembayaran === 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
            <option value="QRIS" {{ $pemesanan->metode_pembayaran === 'QRIS' ? 'selected' : '' }}>QRIS</option>
            @if($pemesanan->metode_pembayaran === 'Midtrans')
            <option value="Midtrans" selected>Midtrans</option>
            @endif
        </select>
    </div>

    <div class="form-group">
        <label for="status_pembayaran">Status Pembayaran</label>
        <select class="form-control" id="status_pembayaran" name="status_pembayaran">
            <option value="Pending" {{ $pemesanan->status_pembayaran === 'Pending' ? 'selected' : '' }}>Pending</option>
            <option value="Sukses" {{ $pemesanan->status_pembayaran === 'Sukses' ? 'selected' : '' }}>Sukses</option>
            <option value="Batal" {{ $pemesanan->status_pembayaran === 'Batal' ? 'selected' : '' }}>Batal</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Update Pemesanan</button>
</form>

<script>
    document.getElementById('lapangan_id').addEventListener('change', updateTotalPrice);
    document.getElementById('durasi').addEventListener('input', updateTotalPrice);
    document.getElementById('waktu').addEventListener('change', updateWaktuSelesai);
    document.getElementById('durasi').addEventListener('input', updateWaktuSelesai);
    document.getElementById('metode_pembayaran').addEventListener('change', syncStatusWithMethod);

    function updateTotalPrice() {
        const lapanganSelect = document.getElementById('lapangan_id');
        const durasiInput = document.getElementById('durasi');
        const totalHargaInput = document.getElementById('total_harga');

        const harga = lapanganSelect.options[lapanganSelect.selectedIndex]?.getAttribute('data-harga');
        const durasi = durasiInput.value;

        if (harga && durasi) {
            const total = Number(harga) * Number(durasi);
            totalHargaInput.value = Number(total).toLocaleString();
        } else {
            totalHargaInput.value = '';
        }
    }

    function updateWaktuSelesai() {
        const waktu = document.getElementById('waktu').value;
        const durasi = parseInt(document.getElementById('durasi').value);
        if (waktu && durasi) {
            const [h, m = '0'] = waktu.split(':');
            const date = new Date(0, 0, 0, parseInt(h) + durasi, parseInt(m));
            const str = `${date.getHours().toString().padStart(2,'0')}:${date.getMinutes().toString().padStart(2,'0')}`;
            document.getElementById('waktu_selesai').value = str;
        }
    }

    function syncStatusWithMethod() {
        const metode = document.getElementById('metode_pembayaran').value;
        const statusSelect = document.getElementById('status_pembayaran');
        if (metode === 'Belum Bayar') {
            statusSelect.value = 'Pending';
        }
    }

    updateTotalPrice();
    updateWaktuSelesai();
    syncStatusWithMethod();
</script>

@endsection