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
        Schema::create('received_payment_email_communications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('received_payment_id');
            // $table->foreign('received_payment_id')->references('id')->on('received_payments')->onDelete('cascade');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_payment_email_communications');
    }
};
