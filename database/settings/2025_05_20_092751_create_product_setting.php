<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('product.tax', "1");
        $this->migrator->add('product.amount', '100');
        $productTypes = [
            'tax' => 'text',
            'amount' => 'number',
        ];

        foreach ($productTypes as $name => $type) {
            \DB::table('settings')
                ->where('group', 'product')
                ->where('name', $name)
                ->update(['type' => $type]);
        }
    }
};
