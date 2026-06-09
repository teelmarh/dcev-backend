<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            // Link to the enrollment transaction that unlocked this application
            $table->foreignId('enrollment_transaction_id')
                ->nullable()
                ->after('passport_photo_path')
                ->constrained('transactions')
                ->nullOnDelete();

            // How the user wants to receive the physical licence
            $table->enum('delivery_method', ['pickup', 'delivery'])
                ->nullable()
                ->after('enrollment_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enrollment_transaction_id');
            $table->dropColumn('delivery_method');
        });
    }
};
