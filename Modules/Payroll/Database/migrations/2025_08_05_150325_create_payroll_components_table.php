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
        Schema::create('payroll_components', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedBigInteger('payroll_component_category_id')->nullable();
            $table->string('component_type');
            $table->string('component_scope_mode');
            $table->string('calculation_type');                              // Fixed Amount, Percentage, Formula
            $table->double('amount')->nullable();                            // If calculation_type = Fixed Amount,
            $table->double('rate')->nullable();                              // If calculation_type = Percentage,
            $table->string('system_build_in_payroll_component')->nullable(); // If calculation_type = Percentage,
            $table->string('formula')->nullable();                           // If calculation_type = Percentage,
            $table->boolean('taxable')->default(false);
            $table->boolean('affects_net_pay')->default(false);
            $table->boolean('recurring')->default(false);
            $table->json('employment_types')->nullable();
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
        Schema::dropIfExists('payroll_components');
    }
};
