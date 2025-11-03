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
        Schema::table('izins', function (Blueprint $table) {
            $table->date('tanggal_mulai')->nullable()->after('tipe');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            $table->string('dokumen')->nullable()->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('izins', function (Blueprint $table) {
            $table->dropColumn('tanggal_mulai');
            $table->dropColumn('tanggal_selesai');
            $table->dropColumn('dokumen');
        });
    }
};
