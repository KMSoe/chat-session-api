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
        Schema::create('payroll_slip_template_applicable_tos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_slip_template_id');
            // $table->foreign('payroll_slip_template_id')->references('id')->on('over_time_settings')->onDelete('cascade');
            $table->enum('scope', ['group', 'department', 'designation', 'employee']);
            $table->unsignedBigInteger('target_id');
            $table->boolean('include_children')->default(true);
            $table->timestamps();

            $table->unique(['payroll_slip_template_id', 'scope', 'target_id'], 'overtime_scope_target_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_slip_template_applicable_tos');
    }
};
