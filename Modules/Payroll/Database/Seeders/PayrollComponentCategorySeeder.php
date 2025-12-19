<?php
namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payroll\App\Models\PayrollComponentCategory;

class PayrollComponentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Salary/Wages',
            'Leave Pay',
            'Director’s Fee',
            'Commission/Fees',
            'Bonus',
            'Back Pay, Payment in Lieu of Notice, Terminal Awards or Gratuities',
            'Certain Payments from Retirement Schemes',
            'Salaries Tax paid by Employer',
            'Education Benefits',
            'Gain realized under Share Option Scheme',
            'Any other Rewards, Allowances or Perquisites (Nature)',
            'Pensions',
            'Monthly Rate of Fixed Income',
            'Monthly Rate of Allowance (e.g. Cost of Living)',
            'Fluctuating Income (e.g. Commission, Bonus, Gratuities)',
            'Payments that have not been declared above but will be made AFTER the employee has left employment (Nature)',
            'Subcontracting Fees',
            'Commission',
            'Writer’s / Contributor’s Fees',
            'Artiste’s Fees',
            'Copyright / Royalties',
            'Consultancy / Management Fees',
            'Service Fees',
            'Nature',
            'Payments that have not been declared above but will be made AFTER the employee has left employment',
        ];

        foreach ($categories as $category) {
            PayrollComponentCategory::create([
                'name'        => $category,
                'description' => null,
                'is_active'   => true,
            ]);
        }
    }
}
