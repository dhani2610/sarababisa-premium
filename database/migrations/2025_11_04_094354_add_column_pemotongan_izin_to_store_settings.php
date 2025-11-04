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
            $table->integer('nominal_potongan_izin')->nullable();
            $table->integer('nominal_potongan_alfa')->nullable();
            $table->integer('nominal_potongan_sakit')->nullable();
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
            $table->dropColumn('nominal_potongan_izin');
            $table->dropColumn('nominal_potongan_alfa');
            $table->dropColumn('nominal_potongan_sakit');
        });
    }
};
