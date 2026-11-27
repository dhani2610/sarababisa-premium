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
        // 1. store_settings
        Schema::table('store_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('store_settings', 'hari_absen')) {
                $table->text('hari_absen')->nullable()->after('jam_pulang');
            }
            if (!Schema::hasColumn('store_settings', 'nominal_potongan_tidak_absen')) {
                $table->integer('nominal_potongan_tidak_absen')->default(0)->nullable()->after('hari_absen');
            }
        });

        // 2. service_transactions
        Schema::table('service_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('service_transactions', 'tipe_diskon')) {
                $table->string('tipe_diskon', 20)->default('nominal')->nullable()->after('diskon');
            }
            if (!Schema::hasColumn('service_transactions', 'nilai_diskon')) {
                $table->decimal('nilai_diskon', 15, 2)->default(0)->nullable()->after('tipe_diskon');
            }
        });

        // 3. orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tipe_diskon')) {
                $table->string('tipe_diskon', 20)->default('nominal')->nullable()->after('discount_amount');
            }
            if (!Schema::hasColumn('orders', 'nilai_diskon')) {
                $table->decimal('nilai_diskon', 15, 2)->default(0)->nullable()->after('tipe_diskon');
            }
        });

        // 4. order_details
        Schema::table('order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('order_details', 'product_discount_type')) {
                $table->string('product_discount_type', 20)->default('fixed')->nullable()->after('product_discount_amount');
            }
            if (!Schema::hasColumn('order_details', 'product_discount_percent')) {
                $table->decimal('product_discount_percent', 8, 2)->default(0)->nullable()->after('product_discount_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            if (Schema::hasColumn('store_settings', 'hari_absen')) {
                $table->dropColumn('hari_absen');
            }
            if (Schema::hasColumn('store_settings', 'nominal_potongan_tidak_absen')) {
                $table->dropColumn('nominal_potongan_tidak_absen');
            }
        });

        Schema::table('service_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('service_transactions', 'tipe_diskon')) {
                $table->dropColumn('tipe_diskon');
            }
            if (Schema::hasColumn('service_transactions', 'nilai_diskon')) {
                $table->dropColumn('nilai_diskon');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'tipe_diskon')) {
                $table->dropColumn('tipe_diskon');
            }
            if (Schema::hasColumn('orders', 'nilai_diskon')) {
                $table->dropColumn('nilai_diskon');
            }
        });

        Schema::table('order_details', function (Blueprint $table) {
            if (Schema::hasColumn('order_details', 'product_discount_type')) {
                $table->dropColumn('product_discount_type');
            }
            if (Schema::hasColumn('order_details', 'product_discount_percent')) {
                $table->dropColumn('product_discount_percent');
            }
        });
    }
};
