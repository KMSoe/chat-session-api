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
        Schema::create('credit_note_invoice_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_note_id');
            // $table->foreign('credit_note_id')->references('id')->on('credit_notes')->onDelete('cascade');
            $table->unsignedBigInteger('invoice_id');
            // $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->date('credit_note_applied_date');
            $table->decimal('payment_amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_invoice_applications');
    }
};
