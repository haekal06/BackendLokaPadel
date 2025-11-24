<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemesanans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');  // Relasi ke tabel users
            $table->unsignedBigInteger('lapangan_id');  // Relasi ke tabel lapangans
            $table->date('tanggal');  // Kolom tanggal
            $table->time('waktu');  // Kolom waktu
            $table->integer('durasi');  // Kolom durasi dalam jam
            $table->decimal('total_harga', 10, 2);  // Kolom total harga
            $table->string('waktu_selesai')->nullable();  // Kolom waktu selesai
            $table->timestamps();
            // Menambahkan foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('lapangan_id')->references('id')->on('lapangans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanans');
    }
};
