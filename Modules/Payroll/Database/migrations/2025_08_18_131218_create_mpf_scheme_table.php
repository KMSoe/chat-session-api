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
        Schema::create('mpf_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // MPF Scheme ID (auto/manual like MPF001)
            $table->string('name'); // Unique registered MPF scheme name
            $table->unsignedBigInteger('trustee_id'); // FK to mpf_trustees
            $table->string('registration_no'); // Registration number issued by MPFA

            $table->string('employer_name'); // Employer name
            $table->string('contact_person'); // Contact person
            $table->string('phone_dial_code'); // Dial Code
            $table->string('phone_no'); // Phone number
            $table->text('address'); // Employer address
            $table->string('employer_participation_no'); // Employer participation number

            $table->decimal('employee_contribution_rate');
            $table->decimal('employer_contribution_rate');

            $table->decimal('minimum_income_level'); // HKD
            $table->decimal('maximum_income_level'); // HKD

            $table->integer('eligibility_waiting_period_days')->default(0);

            $table->boolean('voluntary_contributions_allowed')->default(false);

            $table->decimal('voluntary_rate_employer', 5, 2)->nullable();
            $table->decimal('voluntary_rate_employee', 5, 2)->nullable();

            $table->string('contribution_based_on');

            $table->enum('remittance_file_format', ['XML', 'CSV', 'TXT', 'XLSX', 'PDF'])->default('XML');

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
        Schema::dropIfExists('mpf_schemes');
    }
};
