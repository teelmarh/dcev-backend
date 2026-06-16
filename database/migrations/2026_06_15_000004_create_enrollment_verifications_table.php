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

            $table->unsignedBigInteger('licence_id')->unique();
            $table->foreign('licence_id')->references('id')->on('licences')->cascadeOnDelete();
            $table->unsignedBigInteger('officer_id')->nullable();
            $table->foreign('officer_id')->references('id')->on('users')->noActionOnDelete();

            $table->boolean('physical_presence_confirmed')->nullable();
            $table->boolean('nin_photo_matched')->nullable();
            $table->boolean('age_eligible')->nullable();
            $table->boolean('uploaded_licence_reviewed')->nullable();
            $table->boolean('physical_licence_confirmed')->nullable();

            $table->boolean('has_discrepancy')->default(false);

            $table->string('discrepancy_type')->nullable();
            $table->text('discrepancy_remarks')->nullable();

        
            $table->text('escalation_reason')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_verifications');
    }
};
