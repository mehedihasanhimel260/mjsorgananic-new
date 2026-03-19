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
        Schema::table('users', function (Blueprint $table) {
            $table->string('location_permission', 20)->nullable()->after('password');
            $table->timestamp('last_visit_at')->nullable()->after('location_permission');
            $table->timestamp('last_logged_at')->nullable()->after('last_visit_at');
            $table->text('last_user_agent')->nullable()->after('last_logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'location_permission',
                'last_visit_at',
                'last_logged_at',
                'last_user_agent',
            ]);
        });
    }
};
