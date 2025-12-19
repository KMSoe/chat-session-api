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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('quotation_number')->unique()->nullable();
            $table->string('reference_number')->nullable();
            $table->date('quotation_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('sale_person_id')->nullable();
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
            $table->enum('quotation_status', [ 'draft', 'pending_approval', 'approved', 'rejected', 'sent', 'accepted', 'invoiced', 'declined', 'expired'])->default('draft');
            $table->enum('status', ['pending', 'in_progress', 'approved', 'rejected'])->default('pending'); // approval status
            $table->enum('taxation_level', ['item', 'quote'])->default('item');
            $table->enum('discount_level', ['item', 'quote'])->default('quote');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('quotations');
        Schema::enableForeignKeyConstraints();
    }
};
