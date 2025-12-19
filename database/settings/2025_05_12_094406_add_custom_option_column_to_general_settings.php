<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends SettingsMigration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('option')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('option');
        });
    }
};
