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
        Schema::table('payroll_policies', function (Blueprint $table) {
            $table->enum('pay_cycle_start_day', ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'])->nullable()->after('pay_cycle_start_date');
            $table->boolean('paid_leave_pay')->default(false)->after('prorata_calculation_formula');
            $table->boolean('holiday_pay')->default(false)->after('paid_leave_pay');
            $table->boolean('weekoff_pay')->default(false)->after('holiday_pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_policies', function (Blueprint $table) {
            $table->dropColumn('pay_cycle_start_day');
            $table->dropColumn('paid_leave_pay');
            $table->dropColumn('holiday_pay');
            $table->dropColumn('weekoff_pay');
        });
    }
};
