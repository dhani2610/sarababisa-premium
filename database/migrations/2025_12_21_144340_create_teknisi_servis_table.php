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
        Schema::create('teknisi_servis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_transactions_id')
                  ->constrained('service_transactions')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            // Relasi ke User (Teknisi)
            $table->foreignId('users_id')
                  ->constrained('users')
                  ->onUpdate('cascade');

            $table->string('tipe')->nullable(); // Hardware / Interface

            // Data Keuangan (BigInteger biar aman untuk Rupiah)
            $table->bigInteger('modal_sparepart')->default(0);
            $table->bigInteger('biaya')->default(0);
            $table->bigInteger('profit')->default(0);
            $table->bigInteger('profittoko')->default(0);
            $table->bigInteger('bonus_interface')->default(0);

            $table->integer('persen_teknisi')->default(0); // Misal: 50, 40

            // Kolom JSON untuk menyimpan detail array (History tindakan, harga per item, dll)
            $table->json('tindakan_servis')->nullable(); // Nama-nama tindakan (text)
            $table->json('service_actions')->nullable(); // ID service_actions
            $table->json('products')->nullable();        // ID products (sparepart)
            $table->json('biaya_j')->nullable();         // Rincian biaya per item
            $table->json('modal_j')->nullable();         // Rincian modal per item

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
        Schema::dropIfExists('teknisi_servis');
    }
};
