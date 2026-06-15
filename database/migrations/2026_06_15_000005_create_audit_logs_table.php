<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only audit trail — rows are never updated or deleted.
     * Records every significant officer action with a polymorphic subject reference.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // The officer (or system user) who performed the action
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->noActionOnDelete();

            // Slug describing what happened — e.g. enroll_started, verification_saved,
            // discrepancy_flagged, verification_complete, escalated, application_processed
            $table->string('action');

            // Polymorphic subject — e.g. Licence, Appointment
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');

            // Arbitrary structured context (before/after values, input snapshot, etc.)
            $table->json('payload')->nullable();

            // Request metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
