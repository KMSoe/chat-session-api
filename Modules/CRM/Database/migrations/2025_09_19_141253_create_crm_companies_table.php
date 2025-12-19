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
        Schema::create('crm_companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code')->nullable();
            $table->unsignedBigInteger('logo_file_id')->nullable();
            $table->string('name')->nullable();
            $table->text('domain')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_dial_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('billing_country_id')->nullable();
            $table->unsignedBigInteger('billing_state_id')->nullable();
            $table->string('billing_district')->nullable();
            $table->string('billing_zip_code')->nullable();
            $table->text('billing_address_line_1')->nullable();
            $table->text('billing_address_line_2')->nullable();
            $table->string('billing_phone_dial_code')->nullable();
            $table->string('billing_phone_number')->nullable();
            $table->boolean('shipping_same_as_billing')->default(false);
            $table->unsignedBigInteger('shipping_country_id')->nullable();
            $table->unsignedBigInteger('shipping_state_id')->nullable();
            $table->string('shipping_district')->nullable();
            $table->string('shipping_zip_code')->nullable();
            $table->text('shipping_address_line_1')->nullable();
            $table->text('shipping_address_line_2')->nullable();
            $table->string('shipping_phone_dial_code')->nullable();
            $table->string('shipping_phone_number')->nullable();
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
        Schema::dropIfExists('crm_companies');
    }
};
