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
        Schema::create('bonus_levelings', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->integer('start_rate');
            $table->integer('end_rate');
            $table->integer('nominal_bonus');
            $table->integer('id_jenis_barang');
            $table->integer('id_tipe_os');
            $table->integer('cabang_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonus_levelings');
    }
};
