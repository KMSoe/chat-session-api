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
        Schema::create('project_applicable_tos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            // $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->enum('scope', ['group', 'department', 'designation', 'employee']);
            $table->unsignedBigInteger('target_id');
            $table->boolean('include_children')->default(true);
            $table->timestamps();
            $table->unique(['project_id', 'scope', 'target_id'], 'project_scope_target_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_applicable_tos');
    }
};
