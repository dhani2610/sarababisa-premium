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
        Schema::table('service_transactions', function (Blueprint $table) {
            $table->longText('qc_masuk')->nullable()->change();
            $table->longText('qc_keluar')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_transactions', function (Blueprint $table) {
            $table->string('qc_masuk')->nullable()->change();
            $table->string('qc_keluar')->nullable()->change();
        });
    }
};
