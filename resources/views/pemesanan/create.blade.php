@extends('index')

@section('main-content')
<h1 class="h3 mb-4 text-gray-800">Create New Pemesanan</h1>

@if (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form action="{{ route('pemesanan.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="user_id">User</label>
        <select class="form-control" id="user_id" name="user_id" required>
            @foreach ($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="lapangan_id">Lapangan</label>
        <select class="form-control" id="lapangan_id" name="lapangan_id" required>
            @foreach ($lapangans as $lapangan)
            <option value="{{ $lapangan->id }}" data-harga="{{ $lapangan->harga }}">{{ $lapangan->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="tanggal">Tanggal</label>
        <input type="date" class="form-control" id="tanggal" name="tanggal" required>
    </div>

    <div class="form-group">
        <label for="waktu">Waktu</label>
        <select class="form-control" id="waktu" name="waktu" required>
            @for ($i = 0; $i < 24; $i++)
                <option value="{{ sprintf('%02d:00', $i) }}">{{ sprintf('%02d:00', $i) }}</option>
                @endfor
        </select>
    </div>

    <div class="form-group">
        <label for="durasi">Durasi (jam)</label>
        <input type="number" class="form-control" id="durasi" name="durasi" required min="1">
    </div>

    <div class="form-group">
        <label for="total_harga">Total Harga</label>
        <input type="text" class="form-control" id="total_harga" name="total_harga" readonly>
    </div>

    <div class="form-group">
        <label for="waktu_selesai">Waktu Selesai</label>
        <input type="text" class="form-control" id="waktu_selesai" name="waktu_selesai" readonly>
    </div>

    <div class="form-group">
        <label for="metode_pembayaran">Metode Pembayaran</label>
        <select class="form-control" id="metode_pembayaran" name="metode_pembayaran">
            <option value="Belum Bayar" selected>Belum Bayar</option>
            <option value="Transfer Bank">Transfer Bank</option>
            <option value="QRIS">QRIS</option>
        </select>
    </div>

    <div class="form-group">
        <label for="status_pembayaran">Status Pembayaran</label>
        <select class="form-control" id="status_pembayaran" name="status_pembayaran">
            <option value="Pending" selected>Pending</option>
            <option value="Sukses">Sukses</option>
            <option value="Batal">Batal</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Create</button>
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