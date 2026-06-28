<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teknisi_targets', function (Blueprint $table) {
            // Menambahkan kolom bonus_nominal setelah kolom nominal
            $table->bigInteger('bonus_nominal')->nullable()->after('nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teknisi_targets', function (Blueprint $table) {
            $table->dropColumn('bonus_nominal');
        });
    }
};
