<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payroll_policies')->insert([
            [
                'code'                        => 'Default Policy',
                'name'                        => 'Basic Monthly Payroll',
                'pay_frequency'               => 'monthly',
                'pay_cycle_start_date'        => 1,
                'prorata_calculation_formula' => 'working_days',
                'is_active'                   => true,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ],
        ]);
    }
}
