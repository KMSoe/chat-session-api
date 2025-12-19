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
        Schema::create('employee_tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_calculation_id');
            $table->unsignedBigInteger('employee_id');
            $table->boolean('wholly_or_partly_paid_either')->default(false);
            $table->string('non_hong_kong_company_name')->nullable();
            $table->string('address')->nullable();
            $table->double('amount')->default(0.00);
            $table->text('remarks')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_tax_calculations');
    }
};
