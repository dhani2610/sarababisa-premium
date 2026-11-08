<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDashboardPerformanceIndexes extends Migration
{
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

    public function up()
    {
        // 1. orders
        $this->addIndexIfNotExists('orders', ['cabang_id', 'is_approve', 'tgl_disetujui', 'deleted_at'], 'idx_orders_cab_app_tgl_del');
        $this->addIndexIfNotExists('orders', ['cabang_id', 'tgl_disetujui', 'deleted_at'], 'idx_orders_cab_tgl_del');

        // 2. order_details
        $this->addIndexIfNotExists('order_details', ['users_id', 'deleted_at'], 'idx_od_users_del');
        $this->addIndexIfNotExists('order_details', ['admin_id', 'deleted_at'], 'idx_od_admin_del');
        $this->addIndexIfNotExists('order_details', ['cabang_id', 'created_at', 'deleted_at'], 'idx_od_cab_created_del');

        // 3. service_transactions
        $this->addIndexIfNotExists('service_transactions', ['cabang_id', 'is_approve', 'tgl_disetujui', 'deleted_at'], 'idx_st_cab_app_tgl_del');
        $this->addIndexIfNotExists('service_transactions', ['cabang_id', 'status_servis', 'tgl_ambil', 'deleted_at'], 'idx_st_cab_stat_ambil_del');
        $this->addIndexIfNotExists('service_transactions', ['admin_id', 'deleted_at'], 'idx_st_admin_del');

        // 4. teknisi_servis
        $this->addIndexIfNotExists('teknisi_servis', ['users_id', 'service_transactions_id'], 'idx_ts_user_st');
        $this->addIndexIfNotExists('teknisi_servis', ['service_transactions_id', 'users_id'], 'idx_ts_st_user');

        // 5. expenses, purchases, incidents, refunds, targets, budgets
        $this->addIndexIfNotExists('expenses', ['cabang_id', 'is_approve', 'tgl_disetujui', 'deleted_at'], 'idx_exp_cab_app_tgl_del');
        $this->addIndexIfNotExists('expenses', ['cabang_id', 'created_at', 'deleted_at'], 'idx_exp_cab_created_del');
        $this->addIndexIfNotExists('purchases', ['cabang_id', 'created_at'], 'idx_purch_cab_created');
        $this->addIndexIfNotExists('incidents', ['cabang_id', 'created_at', 'deleted_at'], 'idx_inc_cab_created_del');
        $this->addIndexIfNotExists('refunds', ['cabang_id', 'created_at'], 'idx_ref_cab_created');
        $this->addIndexIfNotExists('targets', ['cabang_id', 'created_at'], 'idx_tgt_cab_created');
        $this->addIndexIfNotExists('budgets', ['cabang_id'], 'idx_bdg_cab');
    }

    public function down()
    {
        $this->dropIndexIfExists('orders', 'idx_orders_cab_app_tgl_del');
        $this->dropIndexIfExists('orders', 'idx_orders_cab_tgl_del');
        $this->dropIndexIfExists('order_details', 'idx_od_users_del');
        $this->dropIndexIfExists('order_details', 'idx_od_admin_del');
        $this->dropIndexIfExists('order_details', 'idx_od_cab_created_del');
        $this->dropIndexIfExists('service_transactions', 'idx_st_cab_app_tgl_del');
        $this->dropIndexIfExists('service_transactions', 'idx_st_cab_stat_ambil_del');
        $this->dropIndexIfExists('service_transactions', 'idx_st_admin_del');
        $this->dropIndexIfExists('teknisi_servis', 'idx_ts_user_st');
        $this->dropIndexIfExists('teknisi_servis', 'idx_ts_st_user');
        $this->dropIndexIfExists('expenses', 'idx_exp_cab_app_tgl_del');
        $this->dropIndexIfExists('expenses', 'idx_exp_cab_created_del');
        $this->dropIndexIfExists('purchases', 'idx_purch_cab_created');
        $this->dropIndexIfExists('incidents', 'idx_inc_cab_created_del');
        $this->dropIndexIfExists('refunds', 'idx_ref_cab_created');
        $this->dropIndexIfExists('targets', 'idx_tgt_cab_created');
        $this->dropIndexIfExists('budgets', 'idx_bdg_cab');
    }
}
