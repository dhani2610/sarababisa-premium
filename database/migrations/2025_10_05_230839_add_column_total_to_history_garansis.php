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
            $table->integer('total_biaya_tindakan')->default(0);
            $table->integer('modal_sparepart')->default(0);
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
            $table->dropColumn('total_biaya_tindakan');
            $table->dropColumn('modal_sparepart');
        });
    }
};
