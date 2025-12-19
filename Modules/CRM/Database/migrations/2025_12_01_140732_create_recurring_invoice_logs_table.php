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
        Schema::create('recurring_invoice_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recurring_invoice_id')->nullable();
            $table->unsignedBigInteger('recurring_invoice_item_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->date('generated_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_logs');
    }
};
