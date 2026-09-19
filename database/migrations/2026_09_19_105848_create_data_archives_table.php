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
        Schema::create('data_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cabang_id')->nullable()->index();
            $table->string('module_key')->index();
            $table->string('module_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->integer('record_count')->default(0);
            $table->string('created_by')->nullable();
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
        Schema::dropIfExists('data_archives');
    }
};
