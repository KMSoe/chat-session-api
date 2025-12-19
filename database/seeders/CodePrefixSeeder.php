<?php

namespace Database\Seeders;

use App\Models\CodePrefix;
use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodePrefixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('code_prefixes')->truncate();
        $quotation_module_id = Module::where('name', 'quotation')->first()?->id;

        CodePrefix::create([
            'module_id' => $quotation_module_id ?? null,
            'module_name' => 'quotation',
            'prefix' => 'QT-2025',
            'next_number' => '001',
            'type' => 'automatic',
        ]);

        $invoice_module_id = Module::where('name', 'invoice')->first()?->id;

        CodePrefix::create([
            'module_id' => $invoice_module_id ?? null,
            'module_name' => 'invoice',
            'prefix' => 'INV-2025',
            'next_number' => '001',
            'type' => 'automatic',
        ]);
    }
}
