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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->float('hours_worked')->default(0)->after('currency_id');
            $table->double('hourly_rate')->default(0);
            $table->double('daily_rate')->default(0);
            $table->double('pay_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('hours_worked');
            $table->dropColumn('hourly_rate');
            $table->dropColumn('daily_rate');
            $table->dropColumn('pay_amount');
        });
    }
};
