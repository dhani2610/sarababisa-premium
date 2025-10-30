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
        Schema::create('izins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ambil dari users table
            $table->enum('tipe', ['izin', 'sakit', 'alfa']);
            $table->text('keterangan')->nullable();
            $table->date('tanggal');
            $table->bigInteger('nominal_potongan')->default(0); // simpan sebagai integer rupiah (dalam rupiah)
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
        Schema::dropIfExists('izins');
    }
};
