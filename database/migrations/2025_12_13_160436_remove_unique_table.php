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
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique('name'); 
        });

        Schema::table('capacities', function (Blueprint $table) {
            $table->dropUnique('name');
        });

        Schema::table('colors', function (Blueprint $table) {
            $table->dropUnique('colors_name_unique');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique('code');
        });

        Schema::table('model_series', function (Blueprint $table) {
            $table->dropUnique('name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_invoice_no_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_nomor_seri_unique');
        });

        Schema::table('service_actions', function (Blueprint $table) {
            $table->dropUnique('nama_tindakan');
        });

        Schema::table('service_transactions', function (Blueprint $table) {
            $table->dropUnique('nomor_servis');
        });

        Schema::table('types', function (Blueprint $table) {
            $table->dropUnique('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
            $table->dropUnique('users_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brands', fn(Blueprint $t) => $t->unique('name', 'name'));
        Schema::table('capacities', fn(Blueprint $t) => $t->unique('name', 'name'));
        Schema::table('colors', fn(Blueprint $t) => $t->unique('name', 'colors_name_unique'));
        Schema::table('inventories', fn(Blueprint $t) => $t->unique('code', 'code'));
        Schema::table('model_series', fn(Blueprint $t) => $t->unique('name', 'name'));
        Schema::table('orders', fn(Blueprint $t) => $t->unique('invoice_no', 'orders_invoice_no_unique'));
        Schema::table('products', fn(Blueprint $t) => $t->unique('nomor_seri', 'products_nomor_seri_unique'));
        Schema::table('service_actions', fn(Blueprint $t) => $t->unique('nama_tindakan', 'nama_tindakan'));
        Schema::table('service_transactions', fn(Blueprint $t) => $t->unique('nomor_servis', 'nomor_servis'));
        Schema::table('types', fn(Blueprint $t) => $t->unique('name', 'name'));
        
        Schema::table('users', function (Blueprint $t) {
            $t->unique('username', 'users_username_unique');
            $t->unique('email', 'users_email_unique');
        });
    }
};