<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_charge_settings', function (Blueprint $table) {
            $table->decimal('free_delivery_min_order_amount', 10, 2)->default(0)->after('custom_delivery_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_charge_settings', function (Blueprint $table) {
            $table->dropColumn('free_delivery_min_order_amount');
        });
    }
};
