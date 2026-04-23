<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 30)->default('unverified')->after('alternative_phone');
        });

        DB::table('users')
            ->whereIn('id', function ($query) {
                $query->select('user_id')
                    ->from('orders')
                    ->whereNotNull('user_id')
                    ->whereIn('order_status', ['delivered', 'partial_delivered']);
            })
            ->update(['status' => 'verified']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
