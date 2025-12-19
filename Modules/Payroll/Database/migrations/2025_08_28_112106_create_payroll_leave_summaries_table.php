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
        Schema::create('payroll_leave_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payroll_id');
            $table->double('total_carried_forward')->default(0);
            $table->double('total_entitled')->default(0);
            $table->double('total_taken')->default(0);
            $table->double('total_adjusted')->default(0);
            $table->double('total_balance')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_leave_summaries');
    }
};
