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
            $table->date('tgl_selesai')->nullable();
            $table->text('keluhan')->nullable();
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
            $table->dropColumn('tgl_selesai');
            $table->dropColumn('keluhan');
        });
    }
};
