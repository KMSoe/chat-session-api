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
        Schema::create('employee_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');

            // Basic Information
            $table->boolean('mpf_exempt')->default(false); 
            $table->enum('account_type', ['REE', 'CEE'])->nullable();
            $table->enum('scheme_type', ['MPF', 'ORSO']);
            $table->unsignedBigInteger('scheme_id');
            $table->date('enrollment_date');
            $table->date('employee_contribution_start_date');
            $table->date('employer_contribution_start_date')->nullable();
            $table->date('retirement_date')->nullable();

            // Contribution Rates, if mpf_exempt is enabled, disalbe below two fields
            $table->decimal('employee_contribution_rate', 5, 2)->default(0);
            $table->decimal('employer_contribution_rate', 5, 2)->default(0);

            $table->decimal('voluntary_rate_employee', 5, 2)->default(0);
            $table->decimal('voluntary_rate_employer', 5, 2)->default(0);

            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_enrollments');
    }
};
