<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->time('scheduled_time')->nullable()->after('scheduled_date');
            $table->time('previous_time')->nullable()->after('previous_date');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['scheduled_time', 'previous_time']);
        });
    }
};
