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
        Schema::table('shifts', function (Blueprint $table) {
            $table->integer('potongan_izin')->default(0);
            $table->integer('potongan_cuti')->default(0);
            $table->integer('potongan_sakit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('potongan_izin');
            $table->dropColumn('potongan_cuti');
            $table->dropColumn('potongan_sakit');
        });
    }
};
