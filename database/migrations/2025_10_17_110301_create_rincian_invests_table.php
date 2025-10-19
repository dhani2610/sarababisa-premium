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
        Schema::create('rincian_invests', function (Blueprint $table) {
            $table->id();
            $table->integer('tipe')->comment('1= masuk, 2= pembagian,3= lain lain');
            $table->integer('id_investor');
            $table->date('tanggal');
            $table->string('nominal');
            $table->string('upload_bukti_tf');
            $table->text('keterangan');
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
        Schema::dropIfExists('rincian_invests');
    }
};
