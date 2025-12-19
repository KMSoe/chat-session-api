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
        Schema::create('credit_note_refund_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_note_id');
            // $table->foreign('credit_note_id')->references('id')->on('credit_notes')->onDelete('cascade');
            $table->decimal('refunded_amount', 12, 2);
            $table->date('refunded_date');
            $table->string('reference_number')->nullable();
            $table->string('payment_mode', 50)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_refund_histories');
    }
};
