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
        Schema::create('task_applicable_tos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            // $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->enum('scope', ['group', 'department', 'designation', 'employee']);
            $table->unsignedBigInteger('target_id');
            $table->boolean('include_children')->default(true);
            $table->timestamps();
            $table->unique(['task_id', 'scope', 'target_id'], 'task_scope_target_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_applicable_tos');
    }
};
