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
        Schema::create('average_daily_wages', function (Blueprint $table) {
            $table->id();
            $table->boolean("exclude_rest_days_from_adw");
            $table->boolean("exclude_holidays_from_adw");
            $table->integer("minimum_employment_period_per_week")->default(0);
            $table->integer("minimum_employment_period_per_month")->default(0);
            $table->text('remarks')->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('average_daily_wages');
    }
};
