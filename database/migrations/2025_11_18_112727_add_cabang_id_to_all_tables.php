<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // daftar tabel yang mau ditambah cabang_id
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            if ($table === 'migrations') {
                continue;
            }

            // skip migrations table & tabel yang sudah punya cabang_id
            if (Schema::hasColumn($table, 'cabang_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                $table->integer('cabang_id')->default(1);
            });
        }
    }

    public function down()
    {
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            if ($table === 'migrations') {
                continue;
            }

            if (Schema::hasColumn($table, 'cabang_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('cabang_id');
                });
            }
        }
    }
};
