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
        Schema::create('average_daily_wage_applicable_tos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('average_daily_wage_id');
            // $table->foreign('average_daily_wage_id')->references('id')->on('average_daily_wages')->onDelete('cascade');
            $table->enum('scope', ['group', 'department', 'designation', 'employee']);
            $table->unsignedBigInteger('target_id');
            $table->boolean('include_children')->default(true);
            $table->timestamps();
            // $table->unique(['average_daily_wage_id', 'scope', 'target_id'], 'average_daily_wage_scope_target_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('average_daily_wage_applicable_tos');
    }
};
