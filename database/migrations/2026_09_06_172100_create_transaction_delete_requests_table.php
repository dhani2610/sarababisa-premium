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
        if (!Schema::hasTable('transaction_delete_requests')) {
            Schema::create('transaction_delete_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabang_id')->default(1);
                $table->string('transaksi_type'); // 'servis' or 'pos'
                $table->unsignedBigInteger('transaksi_id');
                $table->string('transaksi_nomor')->nullable(); // nomor_servis or invoice_no
                $table->text('keterangan')->nullable(); // detail transaksi: nama pelanggan, item, total
                $table->text('alasan')->nullable(); // alasan penghapusan
                $table->unsignedBigInteger('requested_by'); // user id pemohon
                $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
                $table->unsignedBigInteger('approved_by')->nullable(); // user id kepala toko
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('transaction_delete_requests');
    }
};
