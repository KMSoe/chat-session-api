<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\App\Enums\ChatSessionStates;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_uuid')->unique(); 
            $table->string('current_state')->default(ChatSessionStates::STARTED->value);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // session_uuid and current_state columns may be used in filters many times 
            $table->index(['session_uuid', 'current_state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
