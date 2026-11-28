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
        if (Schema::hasTable('shifts') && !Schema::hasColumn('shifts', 'hari_kerja')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->json('hari_kerja')->nullable()->after('nama_shift');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'nominal_insiden_toko')) {
                    $table->unsignedBigInteger('nominal_insiden_toko')->default(0)->after('persen');
                }
                if (!Schema::hasColumn('users', 'persentase_potongan_insiden')) {
                    $table->integer('persentase_potongan_insiden')->default(0)->after('nominal_insiden_toko');
                }
            });
        }

        if (Schema::hasTable('workers')) {
            Schema::table('workers', function (Blueprint $table) {
                if (!Schema::hasColumn('workers', 'nominal_insiden_toko')) {
                    $table->unsignedBigInteger('nominal_insiden_toko')->default(0);
                }
                if (!Schema::hasColumn('workers', 'persentase_potongan_insiden')) {
                    $table->integer('persentase_potongan_insiden')->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('shifts') && Schema::hasColumn('shifts', 'hari_kerja')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->dropColumn('hari_kerja');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'nominal_insiden_toko')) {
                    $table->dropColumn('nominal_insiden_toko');
                }
                if (Schema::hasColumn('users', 'persentase_potongan_insiden')) {
                    $table->dropColumn('persentase_potongan_insiden');
                }
            });
        }

        if (Schema::hasTable('workers')) {
            Schema::table('workers', function (Blueprint $table) {
                if (Schema::hasColumn('workers', 'nominal_insiden_toko')) {
                    $table->dropColumn('nominal_insiden_toko');
                }
                if (Schema::hasColumn('workers', 'persentase_potongan_insiden')) {
                    $table->dropColumn('persentase_potongan_insiden');
                }
            });
        }
    }
};
