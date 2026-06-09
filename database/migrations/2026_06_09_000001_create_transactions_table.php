<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Polymorphic — User (enrollment) or Licence (delivery)
            $table->morphs('transactable');

            $table->enum('type', ['enrollment', 'delivery']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 5)->default('NGN');
            $table->string('reference')->unique();
            $table->string('gateway_reference')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('gateway', ['remita', 'paystack']);
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
