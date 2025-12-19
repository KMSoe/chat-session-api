<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'General');
        $this->migrator->add('general.site_active', true);

        $productTypes = [
            'tax' => 'text',
            'amount' => 'boolean',
        ];

        foreach ($productTypes as $name => $type) {
            \DB::table('settings')
                ->where('group', 'product')
                ->where('name', $name)
                ->update(['type' => $type]);
        }
    }
};
