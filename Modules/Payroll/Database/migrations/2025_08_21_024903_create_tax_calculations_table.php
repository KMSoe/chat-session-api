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
        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_form_id');
            $table->enum('period_type', ['single', 'range']);
            $table->date('date')->nullable();       // if period_type = single
            $table->date('start_date')->nullable(); // if period_type = range
            $table->date('end_date')->nullable();   // if period_type = range
            $table->string('status', 25)->default('Draft'); // Draft, Closed
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_calculations');
    }
};
