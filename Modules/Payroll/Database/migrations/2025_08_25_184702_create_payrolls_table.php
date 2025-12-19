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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date("payroll_month");
            $table->unsignedBigInteger("currency_id")->default(0);
            $table->double('basic_salary')->default(0);
            $table->double('gross_salary')->default(0); // Basic + Allowances + Overtime + Bonuses
            $table->double('total_deductions')->default(0);
            $table->double('net_pay')->default(0);
            $table->string('status', 50)->default('Not Calculated'); // ['Not Calculated',  'Calculated', 'Locked', 'Payslip Generated', 'Payslip Sent']
            $table->date('pay_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
