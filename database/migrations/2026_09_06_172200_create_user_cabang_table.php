<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_cabang')) {
            Schema::create('user_cabang', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('cabang_id');
                $table->timestamps();

                $table->unique(['user_id', 'cabang_id']);
            });

            // Seed existing users with their current cabang_id
            $users = DB::table('users')->whereNotNull('cabang_id')->select('id', 'cabang_id')->get();
            foreach ($users as $user) {
                DB::table('user_cabang')->insertOrIgnore([
                    'user_id' => $user->id,
                    'cabang_id' => $user->cabang_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_cabang');
    }
};
