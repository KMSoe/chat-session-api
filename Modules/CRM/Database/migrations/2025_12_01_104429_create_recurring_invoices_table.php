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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->enum('recurring_level', ['item', 'invoice'])->default('invoice');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('recurring_interval')->default(0);
            $table->enum('recurrence_frequency', ['day', 'week', 'month', 'year'])->nullable();
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('sale_person_id')->nullable();
            $table->enum('preference', ['draft', 'create_and_send'])->default('draft');
            $table->unsignedBigInteger('item_template_id')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->boolean('enable_organization_signature')->default(false);
            $table->boolean('enable_customer_signature')->default(false);
            $table->enum('taxation_level', ['item', 'invoice'])->default('item');
            $table->enum('discount_level', ['item', 'invoice'])->default('invoice');
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('recurring_invoices');
    }
};
