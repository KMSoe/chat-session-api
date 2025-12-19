<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orso_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // MPF Scheme ID (auto/manual like MPF001)
            $table->string('name');                   // Unique registered scheme name
            $table->unsignedBigInteger('trustee_id'); // FK to mpf_trustees
            $table->string('scheme_type');            // Define Contribution or Define Benefit
            $table->string('registration_no');
            $table->string('employer_participation_no');
            $table->boolean('employee_contribution_required')->default(true);
            $table->decimal('employee_contribution_rate')->nullable();
            $table->decimal('employer_contribution_rate');

            // if scheme_type = Define Contribution
            $table->integer('service_duration_from_year')->nullable();
            $table->integer('service_duration_from_months')->nullable();
            $table->integer('service_duration_to_year')->nullable();
            $table->integer('service_duration_to_months')->nullable();
            $table->decimal("vested_percentage_of_employer_contribution")->nullable();

            // if scheme_type = Define Benefit
            $table->string("benefit_formula")->nullable();

            $table->integer('eligibility_waiting_period_days')->default(0);

            $table->boolean('voluntary_contributions_allowed')->default(false);

            $table->decimal('voluntary_rate_employer', 5, 2)->nullable();
            $table->decimal('voluntary_rate_employee', 5, 2)->nullable();

            $table->string('contribution_frequency');
            $table->string('contribution_based_on');

            $table->date('effective_from_date');
            $table->date('termination_date')->nullable();

            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orso_schemes');
    }
};
