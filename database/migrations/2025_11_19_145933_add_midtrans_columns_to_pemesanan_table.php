<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pemesanans', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('status_pembayaran');
            $table->string('customer_first_name')->nullable()->after('snap_token');
            $table->string('customer_last_name')->nullable()->after('customer_first_name');
            $table->string('customer_email')->nullable()->after('customer_last_name');
        });
    }

    public function down()
    {
        Schema::table('pemesanans', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'customer_first_name', 'customer_last_name', 'customer_email']);
        });
    }
};
