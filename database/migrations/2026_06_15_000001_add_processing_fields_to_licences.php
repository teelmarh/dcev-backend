<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            // Officer who processed (approved/rejected/returned) the application
            $table->unsignedBigInteger('processed_by')->nullable()->after('pickup_office_id');
            // Timestamp when the application was processed
            $table->timestamp('processed_at')->nullable()->after('processed_by');

            // FK added separately (officer could be deleted — set null rather than cascade)
            $table->foreign('processed_by')
                ->references('id')->on('users')
                ->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['processed_by', 'processed_at']);
        });
    }
};
