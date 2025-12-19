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
        Schema::create('average_daily_wage_payroll_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('average_daily_wage_id');
            $table->unsignedBigInteger('payroll_component_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('average_daily_wage_payroll_components');
    }
};
