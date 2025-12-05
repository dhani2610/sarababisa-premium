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
        Schema::table('store_settings', function (Blueprint $table) {
            $table->integer('is_profit')->default(0);
            $table->integer('is_profit_produk')->default(0);
            $table->integer('is_modal_produk')->default(0);
            $table->integer('is_bonus_produk')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('is_profit');
            $table->dropColumn('is_profit_produk');
            $table->dropColumn('is_modal_produk');
            $table->dropColumn('is_bonus_produk');
        });
    }
};
