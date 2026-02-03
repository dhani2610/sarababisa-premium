<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['jam_masuk', 'jam_pulang']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->json('jam_masuk')->nullable()->after('nama_shift');
            $table->json('jam_pulang')->nullable()->after('jam_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['jam_masuk', 'jam_pulang']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
        });
    }
};
