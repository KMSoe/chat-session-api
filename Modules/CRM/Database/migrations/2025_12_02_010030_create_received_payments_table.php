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
        Schema::create('received_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('payment_no', 50)->unique();
            $table->decimal('amount_received', 12, 2);
            $table->decimal('bank_charges', 12, 2)->nullable()->default(0.00);
            $table->date('payment_date');
            $table->string('payment_mode', 50);
            $table->date('refunded_date')->nullable();
            $table->string('reference_number', 50)->nullable();
            $table->text('description')->nullable();
            // $table->enum('status', ['draft', 'paid', 'refunded'])->default('draft');
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_payments');
    }
};
