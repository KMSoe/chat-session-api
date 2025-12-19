<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_code')->nullable();
            $table->string('title')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('low');
            $table->unsignedBigInteger('project_id')->default(0);
            $table->unsignedBigInteger('status_id')->default(0);
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('completed_at')->nullable();
            $table->boolean('has_reminder')->default(false);
            $table->string('notification_reminder')->nullable();
            $table->integer('notification_custom_minutes')->nullable();
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('tasks');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
