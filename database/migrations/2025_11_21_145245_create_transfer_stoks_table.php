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
        Schema::create('transfer_stoks', function (Blueprint $table) {
            $table->id();
            $table->integer('dari_cabang_id');
            $table->integer('ke_cabang_id');
            $table->integer('dari_produk_id');
            $table->integer('ke_produk_id');
            $table->integer('stok');
            $table->date('tanggal');
            $table->integer('created_by');
            $table->integer('status')->comment('0 = menunggu persetujuan,1 = sudah disetujui');
            $table->integer('approve_by')->nullable();
            $table->integer('datetime_approve')->nullable();
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
        Schema::dropIfExists('transfer_stoks');
    }
};
