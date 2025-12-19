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
        Schema::create('adw_opening_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->date('effective_date');
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('total_wages_in_period', 10, 2);
            $table->integer('total_worked_days_in_period');
            $table->text('adjustment_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adw_opening_balances');
    }
};
