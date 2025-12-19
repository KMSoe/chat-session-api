<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\App\Models\Project;

class CRMDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ProjectSeeder::class,
            QuotationSeeder::class,
            PaymentTermSeeder::class,
            RecurringInvoiceSeeder::class,
            InvoiceSeeder::class,
        ]);
    }
}
