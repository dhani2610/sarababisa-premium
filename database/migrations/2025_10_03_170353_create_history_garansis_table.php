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
            $table->string('penerima_id');
            $table->string('teknisi_id');
            $table->json('sparepart');
            $table->json('biaya');
            $table->integer('total_biaya');
            $table->text('catatan');
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
