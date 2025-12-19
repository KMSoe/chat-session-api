<?php
namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payroll\App\Models\TaxForm;
use Modules\Payroll\App\Models\TaxFormIncomeCategory;

class IR56FormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $forms = [
            [
                'form_type'   => 'IR56B',
                'sort_order'  => 1,
                'period_type' => 'range',
                'description' => "Employer's Return of Remuneration & Pensions is only applicable for employees still under employment for the year ended 31 March of the relevant year.",
                'fields'      => [
                    ['a', 'Salary/Wages', 1],
                    ['b', 'Leave Pay', 2],
                    ['c', 'Directors Fee', 3],
                    ['d', 'Commission/Fees', 4],
                    ['e', 'Bonus', 5],
                    ['f', 'Back Pay, Payment in Lieu of Notice, Terminal Awards or Gratuities', 6],
                    ['g', 'Certain Payments from Retirement Schemes', 7],
                    ['h', 'Salaries Tax paid by Employer', 8],
                    ['i', 'Education Benefits', 9],
                    ['j', 'Gain realized under Share Option Scheme', 10],
                    ['k', 'Any other Rewards, Allowances or Perquisites Nature', 11],
                    ['l', 'Pensions', 12],
                ],
            ],
            [
                'form_type'   => 'IR56E',
                'sort_order'  => 3,
                'period_type' => 'single',
                'description' => 'To be completed and returned within 3 months from date of commencement of employment. (Only applicable to new employees)',
                'fields'      => [
                    ['a', 'Monthly Rate of Fixed Income', 1],
                    ['b', 'Monthly Rate of Allowance (e.g. Cost of Living)', 2],
                    ['c', 'Fluctuating Income (e.g. Commission, Bonus, Gratuities)', 3],
                ],
            ],
            [
                'form_type'   => 'IR56M',
                'sort_order'  => 5,
                'period_type' => 'range',
                'description' => 'Notification of remuneration paid to persons other than employees',
                'fields'      => [
                    ['1', 'Subcontracting Fees', 1],
                    ['2', 'Commission', 2],
                    ['3', 'Writer\'s / Contributor\'s Fees', 3],
                    ['a', 'Artists\'s Fees', 4],
                    ['b', 'Copyright / Royalties', 5],
                    ['c', 'Consultancy / Management Fees', 6],
                    ['d', 'Service Fees', 7],
                    ['e', 'Nature', 8],
                ],
            ],
            [
                'form_type'   => 'IR56F',
                'sort_order'  => 2,
                'period_type' => 'range',
                'description' => 'To be completed and returned not later than 1 month before date of cessation. If the employee is about to depart from Hong Kong, please complete Form IR56G instead.',
                'fields'      => [
                    ['a', 'Salary/Wages', 1],
                    ['b', 'Leave Pay', 2],
                    ['c', 'Directors Fee', 3],
                    ['d', 'Commission/Fees', 4],
                    ['e', 'Bonus', 5],
                    ['f', 'Back Pay, Payment in Lieu of Notice, Terminal Awards or Gratuities', 6],
                    ['g', 'Certain Payments from Retirement Schemes', 7],
                    ['h', 'Salaries Tax paid by Employer', 8],
                    ['i', 'Education Benefits', 9],
                    ['j', 'Gain realized under Share Option Scheme', 10],
                    ['k', 'Any other Rewards, Allowances or Perquisites Nature', 11],
                    ['l', 'Payments that have not been declared above but will be made AFTER the employee has left employment Nature', 12],
                ],
            ],
            [
                'form_type'   => 'IR56G',
                'sort_order'  => 4,
                'period_type' => 'range',
                'form_name'   => 'To be completed and returned in duplicate NOT LATER THAN 1 MONTH BEFORE the EMPLOYEE’S date of departure. An employer should not make any payment of money or money’s worth to the employee for a period of 1 month from the date of this Notice.',
                'fields'      => [
                    ['a', 'Salary/Wages', 1],
                    ['b', 'Leave Pay', 2],
                    ['c', 'Directors Fee', 3],
                    ['d', 'Commission/Fees', 4],
                    ['e', 'Bonus', 5],
                    ['f', 'Back Pay, Payment in Lieu of Notice, Terminal Awards or Gratuities', 6],
                    ['g', 'Certain Payments from Retirement Schemes', 7],
                    ['h', 'Salaries Tax paid by Employer', 8],
                    ['i', 'Education Benefits', 9],
                    ['j', 'Gain realized under Share Option Scheme', 10],
                    ['k', 'Any other Rewards, Allowances or Perquisites Nature', 11],
                    ['l', 'Payments that have not been declared above but will be made AFTER the employee has left employment Nature', 12],
                ],
            ],
        ];

        foreach ($forms as $formData) {
            $form = TaxForm::create([
                'form_type'   => $formData['form_type'],
                'sort_order'  => $formData['sort_order'],
                'description' => 'IRS Form Settings',
            ]);

            foreach ($formData['fields'] as $field) {
                TaxFormIncomeCategory::create([
                    'tax_form_id' => $form->id,
                    'prefix_code' => $field[0],
                    'name'        => $field[1],
                    'sort_order'  => $field[2],
                ]);
            }
        }
    }
}
