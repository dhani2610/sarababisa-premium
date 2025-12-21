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
        Schema::create('qc_produks', function (Blueprint $table) {
            $table->id();
            $table->integer('id_produk')->nullable();
            $table->longtext('qc_masuk')->nullable();
            $table->longtext('qc_keluar')->nullable();
            $table->integer('pic_masuk')->nullable();
            $table->integer('pic_keluar')->nullable();
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
        Schema::dropIfExists('qc_produks');
    }
};
