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
        Schema::create('employee_tax_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('passport_no')->nullable();
            $table->string('passport_place_of_issue')->nullable();
            $table->string('spouse_full_name')->nullable();
            $table->string('spouse_id_card')->nullable();
            $table->string('spouse_passport_no')->nullable();
            $table->string('spouse_passport_place_of_issue')->nullable();
            $table->string('region_code')->nullable();
            $table->string('principal_employer_name')->nullable();
            $table->string('tax_identity')->nullable();
            $table->string('other_income_name')->nullable();
            $table->boolean('same_as_address')->default(false);
            $table->text('postal_address')->nullable();
            $table->boolean('employer_provides_residence')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_tax_files');
    }
};
