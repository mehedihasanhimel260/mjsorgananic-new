<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('affiliate_minimum_withdraw_amount', 10, 2)->default(500)->after('affiliate_active');
            $table->decimal('affiliate_minimum_order_amount', 10, 2)->default(0)->after('affiliate_minimum_withdraw_amount');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['affiliate_minimum_withdraw_amount', 'affiliate_minimum_order_amount']);
        });
    }
};
