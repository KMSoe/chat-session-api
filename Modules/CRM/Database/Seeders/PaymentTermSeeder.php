<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('crm_payment_terms')->truncate();

        $paymentTerms = [
            ['name' => 'Net 15', 'amount' => 15],
            ['name' => 'Net 30', 'amount' => 30],
            ['name' => 'Net 45', 'amount' => 45],
            ['name' => 'Net 60', 'amount' => 60],
            ['name' => 'Due on Receipt', 'amount' => 0],
        ];

        DB::table('crm_payment_terms')->insert($paymentTerms);

        Schema::enableForeignKeyConstraints();
    }
}