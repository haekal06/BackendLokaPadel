{{-- resources/views/dashboard.blade.php --}}
@extends('index')

@section('main-content')
<h1 class="h3 mb-4 text-gray-800">{{ $title ?? 'Dashboard' }}</h1>

<div class="row">

    {{-- Card Total Pesanan --}}
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Pesanan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $totalPesanan }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Total Pendapatan --}}
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Pendapatan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                        </div>
                        <small class="text-muted">
                            Hanya pesanan dengan pembayaran <strong>Sukses</strong>
                        </small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Keluhan --}}
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Keluhan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $totalKeluhan }}
                        </div>
                        <small class="text-muted">
                            Baru: <strong>{{ $keluhanBaru }}</strong>
                        </small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-life-ring fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection