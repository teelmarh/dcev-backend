<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            // Officer notes when approving, rejecting, or returning an application
            $table->text('processing_notes')->nullable()->after('processed_at');
        });
    }

    public function down(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->dropColumn('processing_notes');
        });
    }
};
