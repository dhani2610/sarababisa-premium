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
        Schema::table('history_garansis', function (Blueprint $table) {
            $table->string('estimasi_pengerjaan')->nullable();
            $table->string('fungsi_masuk')->nullable();
            $table->string('fungsi_keluar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('history_garansis', function (Blueprint $table) {
            $table->dropColumn('estimasi_pengerjaan');
            $table->dropColumn('fungsi_masuk');
            $table->dropColumn('fungsi_keluar');
        });
    }
};
