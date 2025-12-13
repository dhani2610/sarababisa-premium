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
        Schema::table('service_transactions', function (Blueprint $table) {
            $table->longText('foto_masuk')->nullable()->after('kerusakan'); // Menyimpan array JSON foto masuk
            $table->longText('foto_selesai')->nullable()->after('foto_masuk'); // Menyimpan array JSON foto selesai
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_transactions', function (Blueprint $table) {
            $table->dropColumn(['foto_masuk', 'foto_selesai']);
        });
    }
};
