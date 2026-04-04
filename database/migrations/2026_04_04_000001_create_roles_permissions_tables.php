<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['admin_id', 'role_id']);
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });

        $permissions = [
            ['name' => 'Dashboard View', 'slug' => 'dashboard.view'],
            ['name' => 'Products Manage', 'slug' => 'products.manage'],
            ['name' => 'Orders Manage', 'slug' => 'orders.manage'],
            ['name' => 'Users Manage', 'slug' => 'users.manage'],
            ['name' => 'Affiliates Manage', 'slug' => 'affiliates.manage'],
            ['name' => 'Chats Manage', 'slug' => 'chats.manage'],
            ['name' => 'FAQs Manage', 'slug' => 'faqs.manage'],
            ['name' => 'Settings Manage', 'slug' => 'settings.manage'],
            ['name' => 'Access Control Manage', 'slug' => 'access-control.manage'],
        ];

        DB::table('permissions')->insert(array_map(fn ($permission) => $permission + [
            'created_at' => now(),
            'updated_at' => now(),
        ], $permissions));
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('admin_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
