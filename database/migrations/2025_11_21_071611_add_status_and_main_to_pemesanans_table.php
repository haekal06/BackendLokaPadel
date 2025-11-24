<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanans', function (Blueprint $table) {
            // ORDER ID untuk tracking ke Midtrans
            if (!Schema::hasColumn('pemesanans', 'order_id')) {
                $table->string('order_id')->nullable()->after('id');
            }

            // Status teknis (untuk backend / Midtrans)
            if (!Schema::hasColumn('pemesanans', 'status')) {
                $table->string('status')
                    ->default('pending')
                    ->after('total_harga');
            }

            // Status main (Mendatang / Berlangsung / Selesai)
            if (!Schema::hasColumn('pemesanans', 'status_main')) {
                $table->string('status_main')
                    ->default('Mendatang')
                    ->after('waktu_selesai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pemesanans', function (Blueprint $table) {
            if (Schema::hasColumn('pemesanans', 'status_main')) {
                $table->dropColumn('status_main');
            }
            if (Schema::hasColumn('pemesanans', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('pemesanans', 'order_id')) {
                $table->dropColumn('order_id');
            }
        });
    }
};
