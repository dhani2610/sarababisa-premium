<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesToTransactionsTables extends Migration
{
    /**
     * Helper to safely add an index only if it doesn't already exist.
     */
    protected function addIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $col) {
            if (!Schema::hasColumn($table, $col)) {
                return;
            }
        }

        $existing = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (empty($existing)) {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        }
    }

    /**
     * Helper to safely drop an index if it exists.
     */
    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $existing = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (!empty($existing)) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. service_transactions
        $this->addIndexIfNotExists('service_transactions', ['cabang_id', 'status_servis', 'deleted_at'], 'idx_st_cabang_status_del');
        $this->addIndexIfNotExists('service_transactions', ['cabang_id', 'status_servis', 'is_approve', 'deleted_at'], 'idx_st_cabang_approve_del');
        $this->addIndexIfNotExists('service_transactions', ['cabang_id', 'status_servis', 'tipe_status_pembayaran', 'deleted_at'], 'idx_st_cabang_bayar_del');
        $this->addIndexIfNotExists('service_transactions', ['customers_id'], 'idx_st_customers_id');
        $this->addIndexIfNotExists('service_transactions', ['users_id'], 'idx_st_users_id');
        $this->addIndexIfNotExists('service_transactions', ['service_actions_id'], 'idx_st_service_actions_id');
        $this->addIndexIfNotExists('service_transactions', ['nomor_servis'], 'idx_st_nomor_servis');
        $this->addIndexIfNotExists('service_transactions', ['created_at'], 'idx_st_created_at');
        $this->addIndexIfNotExists('service_transactions', ['tgl_disetujui'], 'idx_st_tgl_disetujui');
        $this->addIndexIfNotExists('service_transactions', ['tgl_ambil'], 'idx_st_tgl_ambil');

        // 2. orders
        $this->addIndexIfNotExists('orders', ['cabang_id', 'deleted_at'], 'idx_orders_cabang_del');
        $this->addIndexIfNotExists('orders', ['cabang_id', 'due', 'deleted_at'], 'idx_orders_cabang_due_del');
        $this->addIndexIfNotExists('orders', ['cabang_id', 'tipe_status_pembayaran', 'deleted_at'], 'idx_orders_cabang_bayar_del');
        $this->addIndexIfNotExists('orders', ['customers_id'], 'idx_orders_customers_id');
        $this->addIndexIfNotExists('orders', ['users_id'], 'idx_orders_users_id');
        $this->addIndexIfNotExists('orders', ['invoice_no'], 'idx_orders_invoice_no');
        $this->addIndexIfNotExists('orders', ['created_at'], 'idx_orders_created_at');

        // 3. order_details
        $this->addIndexIfNotExists('order_details', ['orders_id', 'deleted_at'], 'idx_od_orders_del');
        $this->addIndexIfNotExists('order_details', ['products_id'], 'idx_od_products_id');
        $this->addIndexIfNotExists('order_details', ['cabang_id'], 'idx_od_cabang_id');

        // 4. customers
        $this->addIndexIfNotExists('customers', ['cabang_id', 'deleted_at'], 'idx_cust_cabang_del');
        $this->addIndexIfNotExists('customers', ['nama'], 'idx_cust_nama');
        $this->addIndexIfNotExists('customers', ['nomor_hp'], 'idx_cust_nomor_hp');

        // 5. products
        $this->addIndexIfNotExists('products', ['cabang_id', 'deleted_at'], 'idx_prod_cabang_del');
        $this->addIndexIfNotExists('products', ['sub_categories_id'], 'idx_prod_subcat_id');

        // 6. model_series
        $this->addIndexIfNotExists('model_series', ['cabang_id', 'brands_id', 'deleted_at'], 'idx_ms_cabang_brand_del');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIndexIfExists('service_transactions', 'idx_st_cabang_status_del');
        $this->dropIndexIfExists('service_transactions', 'idx_st_cabang_approve_del');
        $this->dropIndexIfExists('service_transactions', 'idx_st_cabang_bayar_del');
        $this->dropIndexIfExists('service_transactions', 'idx_st_customers_id');
        $this->dropIndexIfExists('service_transactions', 'idx_st_users_id');
        $this->dropIndexIfExists('service_transactions', 'idx_st_service_actions_id');
        $this->dropIndexIfExists('service_transactions', 'idx_st_nomor_servis');
        $this->dropIndexIfExists('service_transactions', 'idx_st_created_at');
        $this->dropIndexIfExists('service_transactions', 'idx_st_tgl_disetujui');
        $this->dropIndexIfExists('service_transactions', 'idx_st_tgl_ambil');

        $this->dropIndexIfExists('orders', 'idx_orders_cabang_del');
        $this->dropIndexIfExists('orders', 'idx_orders_cabang_due_del');
        $this->dropIndexIfExists('orders', 'idx_orders_cabang_bayar_del');
        $this->dropIndexIfExists('orders', 'idx_orders_customers_id');
        $this->dropIndexIfExists('orders', 'idx_orders_users_id');
        $this->dropIndexIfExists('orders', 'idx_orders_invoice_no');
        $this->dropIndexIfExists('orders', 'idx_orders_created_at');

        $this->dropIndexIfExists('order_details', 'idx_od_orders_del');
        $this->dropIndexIfExists('order_details', 'idx_od_products_id');
        $this->dropIndexIfExists('order_details', 'idx_od_cabang_id');

        $this->dropIndexIfExists('customers', 'idx_cust_cabang_del');
        $this->dropIndexIfExists('customers', 'idx_cust_nama');
        $this->dropIndexIfExists('customers', 'idx_cust_nomor_hp');

        $this->dropIndexIfExists('products', 'idx_prod_cabang_del');
        $this->dropIndexIfExists('products', 'idx_prod_subcat_id');

        $this->dropIndexIfExists('model_series', 'idx_ms_cabang_brand_del');
    }
}
