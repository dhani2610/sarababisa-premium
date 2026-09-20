<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = config('webpush.table_name') ?: 'push_subscriptions';
        $connection = config('webpush.database_connection') ?: config('database.default');

        if (!Schema::connection($connection)->hasTable($tableName)) {
            Schema::connection($connection)->create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->morphs('subscribable');
                $table->string('endpoint', 500)->unique();
                $table->string('public_key')->nullable();
                $table->string('auth_token')->nullable();
                $table->string('content_encoding')->nullable();
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
        $tableName = config('webpush.table_name') ?: 'push_subscriptions';
        $connection = config('webpush.database_connection') ?: config('database.default');

        Schema::connection($connection)->dropIfExists($tableName);
    }
}
