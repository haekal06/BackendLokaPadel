<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bantuans', function (Blueprint $table) {
            // kolom catatan_admin boleh kosong
            $table->text('catatan_admin')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('bantuans', function (Blueprint $table) {
            $table->dropColumn('catatan_admin');
        });
    }
};
