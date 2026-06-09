<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('family');        // family name
            $table->string('type');          // pilot | cabin_crew | flight_dispatch | atc | atsep | aso | ame

            $table->string('application_type')->default('data_capture');

            // licence identifiers
            $table->string('licence_number')->nullable();
            $table->date('initial_issue_date');
            $table->date('last_renewal_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('active'); // active | suspended | revoked | expired

            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('hair_colour')->nullable();
            $table->string('eye_colour')->nullable();

            $table->boolean('has_prior_licence')->default(false);
            $table->boolean('prior_licence_suspended')->nullable();
            $table->date('prior_licence_suspended_date')->nullable();
            $table->string('prior_licence_type')->nullable();
            $table->string('prior_licence_number')->nullable();
            $table->date('prior_licence_issued_date')->nullable();

            $table->boolean('medical_cert_held')->default(false);
            $table->string('medical_cert_class')->nullable();
            $table->date('medical_cert_date')->nullable();
            $table->string('medical_examiner_name')->nullable();

            // Identification — auto-populated from NIN if user is nin_verified
            $table->string('id_form')->nullable();
            $table->string('id_number')->nullable();

            // Uploaded documents
            $table->string('licence_document_path')->nullable();
            $table->string('passport_photo_path')->nullable();

            // Payment & delivery — added inline (no separate alter migration)
            $table->foreignId('enrollment_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();
            $table->enum('delivery_method', ['pickup', 'delivery'])->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licences');
    }
};
