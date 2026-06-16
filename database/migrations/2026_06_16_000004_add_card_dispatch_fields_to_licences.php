<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->string('pickup_code', 10)->nullable()->after('pickup_office_id');
            $table->timestamp('notified_at')->nullable()->after('pickup_code');
        });

        // SQL Server does not allow a standard unique index with multiple NULLs.
        // Use a filtered unique index that only applies when pickup_code is not null.
        DB::statement(
            'CREATE UNIQUE INDEX licences_pickup_code_unique ON licences (pickup_code) WHERE pickup_code IS NOT NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS licences_pickup_code_unique ON licences');

        Schema::table('licences', function (Blueprint $table) {
            $table->dropColumn(['pickup_code', 'notified_at']);
        });
    }
};
