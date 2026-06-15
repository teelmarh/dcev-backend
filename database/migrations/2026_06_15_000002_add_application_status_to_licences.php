<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Application processing workflow states (separate from licence validity status):
     *   submitted    — application submitted by applicant, awaiting officer review
     *   under_review — an officer has claimed and is actively reviewing the application
     *   approved     — officer approved; licence issued / ready for delivery
     *   rejected     — officer rejected; applicant must re-apply
     *   returned     — officer returned for corrections; applicant can resubmit
     */
    public function up(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->string('application_status')->default('submitted')->after('application_type');
        });
    }

    public function down(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->dropColumn('application_status');
        });
    }
};
