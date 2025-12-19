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
        Schema::create('employee_residences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->year('period');
            $table->string('nature')->nullable();
            $table->date('start_time')->nullable();
            $table->date('end_time')->nullable();
            $table->decimal('rental_paid_by_employer', 15, 2)->default(0);
            $table->decimal('rental_paid_by_employee', 15, 2)->default(0);
            $table->decimal('rental_refunded_to_employee', 15, 2)->default(0);
            $table->decimal('rental_paid_to_employer', 15, 2)->default(0);
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_residences');
    }
};
