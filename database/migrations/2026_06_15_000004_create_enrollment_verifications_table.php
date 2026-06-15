<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_verifications', function (Blueprint $table) {
            $table->id();

            // One verification record per application — enforced by unique FK
            $table->unsignedBigInteger('licence_id')->unique();
            $table->foreign('licence_id')->references('id')->on('licences')->cascadeOnDelete();

            // Officer who initiated the enrollment
            $table->unsignedBigInteger('officer_id')->nullable();
            $table->foreign('officer_id')->references('id')->on('users')->noActionOnDelete();

            // ---------------------------------------------------------
            // 5-point verification checklist (nullable = not yet checked)
            // ---------------------------------------------------------
            $table->boolean('physical_presence_confirmed')->nullable();
            $table->boolean('nin_photo_matched')->nullable();
            $table->boolean('age_eligible')->nullable();
            $table->boolean('uploaded_licence_reviewed')->nullable();
            $table->boolean('physical_licence_confirmed')->nullable();

            // ---------------------------------------------------------
            // Discrepancy
            // ---------------------------------------------------------
            $table->boolean('has_discrepancy')->default(false);

            // photo_mismatch | age_issue | document_invalid | licence_mismatch | other
            $table->string('discrepancy_type')->nullable();
            $table->text('discrepancy_remarks')->nullable();

            // ---------------------------------------------------------
            // Escalation
            // ---------------------------------------------------------
            $table->text('escalation_reason')->nullable();

            // Set when all checks pass and officer clicks Proceed
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_verifications');
    }
};
