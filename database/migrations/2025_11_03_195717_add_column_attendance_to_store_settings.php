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
            $table->integer('active_setting_absensi')->default(0);//0=inactive,1=active
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('active_setting_absensi');
            $table->dropColumn('jam_masuk');
            $table->dropColumn('jam_pulang');
        });
    }
};
