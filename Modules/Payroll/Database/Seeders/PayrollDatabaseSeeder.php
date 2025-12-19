<?php
namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;

class PayrollDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PayrollComponentCategorySeeder::class,
            PayrollComponentSeeder::class,
            SystemDefaultPayrollComponentSeeder::class,

            IR56FormsSeeder::class,

            MPFTrusteeSeeder::class,
            MPFSchemeSeeder::class,
            ORSOSchemeSeeder::class,
            EnrollmentSeeder::class,

            AverageDailyWageSeeder::class,
            PayrollPolicySeeder::class,
            PayrollSlipTemplateSeeder::class,
            PayrollSeeder::class,
        ]);
    }
}
