<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regional_offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('state');
            $table->string('city');
            $table->text('address');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('daily_capacity')->default(96);
            $table->time('working_hours_start')->default('09:00:00');
            $table->time('working_hours_end')->default('16:00:00');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // FK deferred: licences.pickup_office_id references regional_offices which is created after licences
        Schema::table('licences', function (Blueprint $table) {
            $table->foreign('pickup_office_id')
                  ->references('id')->on('regional_offices')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->dropForeign(['pickup_office_id']);
        });

        Schema::dropIfExists('regional_offices');
    }
};
