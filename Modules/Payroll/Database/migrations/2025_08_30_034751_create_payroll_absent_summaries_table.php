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
        Schema::create('payroll_absent_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payroll_id');
            $table->double('days')->default(0);
            $table->double('daily_rate')->default(0);
            $table->double('amount')->default(0);
            $table->json('absent_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_absent_summaries');
    }
};
