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
        Schema::create('data_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cabang_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('action')->index(); // BACKUP, HAPUS, RESTORE, DOWNLOAD_ARSIP, HAPUS_ARSIP
            $table->string('module_key')->nullable()->index();
            $table->string('module_name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('record_count')->default(0);
            $table->string('file_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_audit_logs');
    }
};
