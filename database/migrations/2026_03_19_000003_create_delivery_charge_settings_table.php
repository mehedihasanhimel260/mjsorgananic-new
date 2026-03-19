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
        Schema::create('delivery_charge_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('inside_dhaka_delivery_charge', 10, 2)->default(0);
            $table->decimal('outside_dhaka_delivery_charge', 10, 2)->default(0);
            $table->decimal('custom_delivery_charge', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_charge_settings');
    }
};
