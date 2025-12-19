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
        Schema::create('employee_tax_calculation_income_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_calculation_id');
            $table->unsignedBigInteger('employee_tax_calculation_id');
            $table->unsignedBigInteger('tax_form_income_category_id');
            $table->double('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_tax_calculation_income_details');
    }
};
