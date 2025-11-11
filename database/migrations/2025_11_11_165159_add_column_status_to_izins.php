<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up()
    {
        // Ubah enum 'tipe' jadi termasuk 'cuti'
        DB::statement("ALTER TABLE izins MODIFY tipe ENUM('izin', 'sakit', 'alfa', 'cuti') NOT NULL");

        // Tambah kolom status kalau belum ada
        Schema::table('izins', function (Blueprint $table) {
            if (!Schema::hasColumn('izins', 'status')) {
                $table->integer('status')->default(0)->after('nominal_potongan');
            }
        });
    }

    /**
     * Balik migrasi.
     */
    public function down()
    {
        // Kembalikan enum ke versi sebelumnya
        DB::statement("ALTER TABLE izins MODIFY tipe ENUM('izin', 'sakit', 'alfa') NOT NULL");

        Schema::table('izins', function (Blueprint $table) {
            if (Schema::hasColumn('izins', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
