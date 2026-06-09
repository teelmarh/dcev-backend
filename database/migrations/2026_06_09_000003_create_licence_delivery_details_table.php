<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licence_delivery_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('licence_id')
                ->unique()
                ->constrained('licences')
                ->cascadeOnDelete();

            // Recipient details (may differ from applicant)
            $table->string('recipient_name');
            $table->string('recipient_phone', 20);

            // Delivery address
            $table->string('address_line');
            $table->string('city');
            $table->string('state');
            $table->string('lga')->nullable();
            $table->string('postal_code', 20)->nullable();

            $table->text('courier_instructions')->nullable();

            // Filled after delivery payment is confirmed
            $table->foreignId('delivery_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licence_delivery_details');
    }
};
