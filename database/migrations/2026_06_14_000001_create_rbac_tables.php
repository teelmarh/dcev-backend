<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Group ↔ Permission pivot (no timestamps needed)
        Schema::create('user_group_permission', function (Blueprint $table) {
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['user_group_id', 'permission_id']);
        });

        // Group ↔ User pivot
        Schema::create('user_group_user', function (Blueprint $table) {
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['user_group_id', 'user_id']);
        });

        // Direct user ↔ permission overrides (bypasses groups)
        Schema::create('user_permission', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permission');
        Schema::dropIfExists('user_group_user');
        Schema::dropIfExists('user_group_permission');
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('permissions');
    }
};
