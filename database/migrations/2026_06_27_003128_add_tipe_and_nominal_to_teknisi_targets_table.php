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
        Schema::table('teknisi_targets', function (Blueprint $table) {
            // Menambahkan tipe (item atau nominal)
            $table->enum('tipe', ['item', 'nominal'])->default('item')->after('users_id');
            // Menambahkan kolom nominal (bigInteger agar cukup menampung angka besar)
            $table->bigInteger('nominal')->nullable()->after('item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teknisi_targets', function (Blueprint $table) {
            $table->dropColumn(['tipe', 'nominal']);
        });
    }
};
