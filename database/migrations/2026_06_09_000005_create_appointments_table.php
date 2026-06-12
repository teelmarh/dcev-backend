<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('licence_id')
                ->constrained('licences')
                ->noActionOnDelete();

            $table->foreignId('regional_office_id')
                ->constrained('regional_offices')
                ->noActionOnDelete();

            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();

            // For audit trail on reschedules
            $table->date('previous_date')->nullable();
            $table->time('previous_time')->nullable();

            $table->enum('status', ['pending', 'confirmed', 'rescheduled', 'completed', 'cancelled'])
                ->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
