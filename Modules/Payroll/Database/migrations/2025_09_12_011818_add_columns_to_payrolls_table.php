<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Payroll\App\Enums\PayFrequencyTypes;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('pay_type')->default(PayFrequencyTypes::MONTHLY->value)->after('payroll_month');
            $table->date('pay_cycle_start_date')->nullable();
            $table->date('pay_cycle_end_date')->nullable();
            $table->json('daily_pay_dates')->nullable();
            $table->json('hourly_pay_dates')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('pay_type');
            $table->dropColumn('pay_cycle_start_date');
            $table->dropColumn('pay_cycle_end_date');
            $table->dropColumn('daily_pay_dates');
            $table->dropColumn('hourly_pay_dates');
        });
    }
};
