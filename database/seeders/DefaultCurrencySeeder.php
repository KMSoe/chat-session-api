<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Modules\Currency\App\Models\Currency;
use Modules\Inventory\App\Models\InventorySetting;

class DefaultCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'currency_name' => 'HongKong Dollar',
                'currency_code' => 'HKD',
                'exchange_rate' => 1,
            ],
            [
                'currency_name' => 'US Dollar',
                'currency_code' => 'USD',
                'exchange_rate' => 0.128690,
            ],
            [
                'currency_name' => 'Euro',
                'currency_code' => 'EUR',
                'exchange_rate' => 0.117495,
            ],
            [
                'currency_name' => 'British Pound',
                'currency_code' => 'GBP',
                'exchange_rate' => 0.100833,
            ],
            [
                'currency_name' => 'Indian Rupee',
                'currency_code' => 'INR',
                'exchange_rate' => 11.072836,
            ],
            [
                'currency_name' => 'Australian Dollar',
                'currency_code' => 'AUD',
                'exchange_rate' => 0.212328,
            ],
            [
                'currency_name' => 'Canadian Dollar',
                'currency_code' => 'CAD',
                'exchange_rate' => 0.182517,
            ],
            [
                'currency_name' => 'Singapore Dollar',
                'currency_code' => 'SGD',
                'exchange_rate' => 0.173767,
            ],
            [
                'currency_name' => 'Swiss Franc',
                'currency_code' => 'CHF',
                'exchange_rate' => 0.110371,
            ],
            [
                'currency_name' => 'Malaysian Ringgit',
                'currency_code' => 'MYR',
                'exchange_rate' => 0.577710,
            ],
            [
                'currency_name' => 'Japanese Yen',
                'currency_code' => 'JPY',
                'exchange_rate' => 18.919120,
            ],
        ];

        foreach ($currencies as $key => $currency) {
            Currency::updateOrCreate([
                'currency_code' => $currency['currency_code'],
            ], [
                'currency_name' => $currency['currency_name'],
                'exchange_rate' => $currency['exchange_rate'],
            ]);
        }

        $inventorySetting = InventorySetting::first();
        if (empty($inventorySetting)) {
            $inventorySetting = new InventorySetting();
        }
        $defaultCurrency = Currency::where('currency_code', 'HKD')->first();
        $inventorySetting->default_currency_id = $defaultCurrency->id;
        $inventorySetting->save();
        Log::info('Success');
        
    }
}