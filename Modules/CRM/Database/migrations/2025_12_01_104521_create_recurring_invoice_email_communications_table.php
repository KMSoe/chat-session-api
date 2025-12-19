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
        Schema::create('recurring_invoice_email_communications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recurring_invoice_id');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->timestamps();
            
            $table->foreign('recurring_invoice_id', 'ri_email_comm_ri_id_fk')
                  ->references('id')
                  ->on('recurring_invoices')
                  ->onDelete('cascade');
            
            $table->foreign('contact_id', 'ri_email_comm_contact_id_fk')
                  ->references('id')
                  ->on('contacts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_email_communications');
    }
};
