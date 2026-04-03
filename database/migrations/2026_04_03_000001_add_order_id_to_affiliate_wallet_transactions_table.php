<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_wallet_transactions', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('affiliate_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_wallet_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
