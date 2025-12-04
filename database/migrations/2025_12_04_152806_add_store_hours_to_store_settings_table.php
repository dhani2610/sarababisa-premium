<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            // is_close tipe integer (0 = Non-aktif, 1 = Aktif)
            $table->integer('is_close')->default(0)->nullable();
            $table->time('time_open_toko')->nullable();
            $table->time('time_close_toko')->nullable();
        });
    }

    public function down()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['is_close', 'time_open_toko', 'time_close_toko']);
        });
    }
};
