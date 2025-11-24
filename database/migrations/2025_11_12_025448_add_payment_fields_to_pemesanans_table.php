<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pemesanans', function (Blueprint $table) {
            $table->string('status_pembayaran')
                ->default('Menunggu Pembayaran')
                ->after('waktu_selesai');
            $table->string('metode_pembayaran')
                ->default('Belum Bayar')
                ->after('status_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanans', function (Blueprint $table) {
            $table->dropColumn(['status_pembayaran', 'metode_pembayaran']);
        });
    }
};
