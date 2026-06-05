<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shared training/basis columns applied to every detail table
        $addSharedColumns = function (Blueprint $table) {
            $table->id();
            $table->foreignId('licence_id')->constrained()->cascadeOnDelete();

            $table->date('knowledge_test_date')->nullable();

            $table->date('skill_test_date')->nullable();
            $table->string('skill_test_aircraft')->nullable();
            $table->string('skill_test_total_time')->nullable();

            $table->string('ato_name')->nullable();
            $table->string('ato_location')->nullable();
            $table->string('ato_number')->nullable();
            $table->string('ato_course')->nullable();
            $table->date('ato_graduation_date')->nullable();

            $table->string('foreign_country')->nullable();
            $table->string('foreign_licence_grade')->nullable();
            $table->string('foreign_licence_number')->nullable();
            $table->string('foreign_ratings')->nullable();

            $table->timestamps();
        };

        // FCL — Pilot
        Schema::create('licence_pilot_details', function (Blueprint $table) use ($addSharedColumns) {
            $addSharedColumns($table);
            $table->string('ratings')->nullable();
            $table->string('aircraft_categories')->nullable();
            $table->text('endorsements')->nullable();
        });

        // FCL — Cabin Crew
        Schema::create('licence_cabin_crew_details', function (Blueprint $table) use ($addSharedColumns) {
            $addSharedColumns($table);
            $table->string('operator')->nullable();
            $table->string('aircraft_types')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
        });

        // FCL — Flight Dispatch
        Schema::create('licence_flight_dispatch_details', function (Blueprint $table) use ($addSharedColumns) {
            $addSharedColumns($table);
            $table->string('ratings')->nullable();
        });

        // ANS — ATC (Air Traffic Control)
        Schema::create('licence_atc_details', function (Blueprint $table) use ($addSharedColumns) {
            $addSharedColumns($table);
            $table->string('ratings')->nullable();
            $table->string('unit')->nullable();
            $table->text('endorsements')->nullable();
        });

        // ANS — ATSEP (Air Traffic Safety Electronics Personnel)
        Schema::create('licence_atsep_details', function (Blueprint $table) use ($addSharedColumns) {
            $addSharedColumns($table);
            $table->string('ratings')->nullable();
        });

        // ANS — ASO (Aerodrome Safety Officer)
        Schema::create('licence_aso_details', function (Blueprint $table) use ($addSharedColumns) {
            $addSharedColumns($table);
            $table->string('aerodrome_category')->nullable();
        });

        // AMEL — AME (Aircraft Maintenance Engineer)
        Schema::create('licence_ame_details', function (Blueprint $table) use ($addSharedColumns) {
            $addSharedColumns($table);
            // Ratings by discipline (comma-separated sub-ratings within each group)
            // Airframe: airframe, unpressurized_metal_airframe, pressurized_metal_airframe, basic_helicopter_airframe
            $table->string('airframe_ratings')->nullable();
            // Powerplant: powerplant, piston_engine, gas_turbine_engine, others
            $table->string('powerplant_ratings')->nullable();
            // Avionics: avionics, dc_electrics, ac_electrics_frequency_wild, ac_electrics_constant_frequency,
            //           aircraft_general_instrument, autopilot, airborne_radio_radar,
            //           direct_reading_compass, remote_reading_compass
            $table->string('avionics_ratings')->nullable();
            $table->string('aircraft_types')->nullable();
            $table->text('scope_of_work')->nullable();
            // Employment information (blocks P, Q, R on the AMEL form)
            $table->string('employer_name')->nullable();
            $table->string('employer_city_state')->nullable();
            $table->string('employed_as')->nullable(); // Engineer | Technician | OJT | Others
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licence_ame_details');
        Schema::dropIfExists('licence_aso_details');
        Schema::dropIfExists('licence_atsep_details');
        Schema::dropIfExists('licence_atc_details');
        Schema::dropIfExists('licence_flight_dispatch_details');
        Schema::dropIfExists('licence_cabin_crew_details');
        Schema::dropIfExists('licence_pilot_details');
    }
};
