<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_captures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('licence_id')
                ->unique()
                ->constrained('licences')
                ->cascadeOnDelete();

            $table->foreignId('captured_by')
                ->constrained('users')
                ->noActionOnDelete();

            $table->string('photo_path')->nullable();
            $table->text('left_index_wsq')->nullable();   // base64 WSQ fingerprint
            $table->text('right_index_wsq')->nullable();  // base64 WSQ fingerprint
            $table->string('signature_path')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_captures');
    }
};
