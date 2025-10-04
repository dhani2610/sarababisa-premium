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
        Schema::create('history_garansis', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('service_id');
            $table->integer('penerima_id');
            $table->integer('teknisi_id');
            $table->json('tindakan');
            $table->json('sparepart')->nullable();
            $table->integer('total_biaya');
            $table->text('catatan');
            $table->integer('status');
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
        Schema::dropIfExists('history_garansis');
    }
};
