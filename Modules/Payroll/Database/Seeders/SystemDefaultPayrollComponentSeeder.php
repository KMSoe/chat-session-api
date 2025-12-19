<?php
namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\App\Enums\PayrollComponentCalculationTypes;
use Modules\Payroll\App\Enums\PayrollComponentScopeModes;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Modules\Payroll\App\Models\PayrollComponent;

class SystemDefaultPayrollComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Basic Salary, EARNING
        // Weekly Pay Rate, EARNING
        // Daily Pay Rate, EARNING
        // Hourly Pay Rate, EARNING

        // Overtime Pay, EARNING
        // Leave Pay, EARNING

        // Late Minutes Deduction
        // Early Out Deduction
        // Unpaid Leave Deduction,  DEDUCTION
        // Absent Deduction,  DEDUCTION
        // MPF (Employee) DEDUCTION
        // MPF (Employer) DEDUCTION,
        // ORSO (Employee) DEDUCTION
        // ORSO (Employer) DEDUCTION

        // Gross Salary, EARNING
        // Total Deduction, DEDUCTION
        // Net Salary, EARNING

        DB::table('payroll_components')->where('is_system_default', 1)->truncate();

        $components = [
            [
                'code'            => 'BASIC_SALARY',
                'name'            => 'Basic Salary',
                'component_type'  => PayrollComponentTypes::EARNING->value,
                'formula'         => 'BASIC_SALARY',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'WEEKLY_PAY_RATE',
                'name'            => 'WEEKLY PAY RATE',
                'component_type'  => PayrollComponentTypes::EARNING->value,
                'formula'         => 'WEEKLY_PAY_RATE',
                'affects_net_pay' => true,
            ],
           [
                'code'            => 'DAILY_PAY_RATE',
                'name'            => 'DAILY PAY RATE',
                'component_type'  => PayrollComponentTypes::EARNING->value,
                'formula'         => 'DAILY_PAY_RATE',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'HOURLY_PAY_RATE',
                'name'            => 'HOURLY PAY RATE',
                'component_type'  => PayrollComponentTypes::EARNING->value,
                'formula'         => 'HOURLY_PAY_RATE',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'OVERTIME_PAY',
                'name'            => 'Overtime Pay',
                'component_type'  => PayrollComponentTypes::EARNING->value,
                'formula'         => 'OVERTIME_PAY',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'LEAVE_PAY',
                'name'            => 'Leave Pay',
                'component_type'  => PayrollComponentTypes::EARNING->value,
                'formula'         => 'LEAVE_PAY',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'HOLIDAY_PAY',
                'name'            => 'HOLIDAY Pay',
                'component_type'  => PayrollComponentTypes::EARNING->value,
                'formula'         => 'HOLIDAY_PAY',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'WEEK_OFF_PAY',
                'name'            => 'WEEK_OFF Pay',
                'component_type'  => PayrollComponentTypes::EARNING->value,
                'formula'         => 'WEEK_OFF_PAY',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'LATE_MINUTES_DEDUCTION',
                'name'            => 'Late Minutes Deduction',
                'component_type'  => PayrollComponentTypes::DEDUCTION->value,
                'formula'         => 'LATE_MINUTES_DEDUCTION',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'EARLY_OUT_DEDUCTION',
                'name'            => 'Early Out Deduction',
                'component_type'  => PayrollComponentTypes::DEDUCTION->value,
                'formula'         => 'EARLY_OUT_DEDUCTION',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'UNPAID_LEAVE_DEDUCTION',
                'name'            => 'Unpaid Leave Deduction',
                'component_type'  => PayrollComponentTypes::DEDUCTION->value,
                'formula'         => 'UNPAID_LEAVE_DEDUCTION',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'ABSENT_DEDUCTION',
                'name'            => 'Absent Deduction',
                'component_type'  => PayrollComponentTypes::DEDUCTION->value,
                'formula'         => 'ABSENT_DEDUCTION',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'MPF_EMPLOYEE',
                'name'            => 'MPF (Employee)',
                'component_type'  => PayrollComponentTypes::DEDUCTION->value,
                'formula'         => 'MPF_EMPLOYEE',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'MPF_EMPLOYER',
                'name'            => 'MPF (Employer)',
                'component_type'  => PayrollComponentTypes::DEDUCTION->value,
                'formula'         => 'MPF_EMPLOYER',
                'affects_net_pay' => false,
            ],
            [
                'code'            => 'ORSO_EMPLOYEE',
                'name'            => 'ORSO (Employee)',
                'component_type'  => PayrollComponentTypes::DEDUCTION->value,
                'formula'         => 'ORSO_EMPLOYEE',
                'affects_net_pay' => true,
            ],
            [
                'code'            => 'ORSO_EMPLOYER',
                'name'            => 'ORSO (Employer)',
                'component_type'  => PayrollComponentTypes::DEDUCTION->value,
                'formula'         => 'ORSO_EMPLOYER',
                'affects_net_pay' => false,
            ],
            // [
            //     'code'            => 'GROSS_SALARY',
            //     'name'            => 'Gross Salary',
            //     'component_type'  => PayrollComponentTypes::EARNING->value,
            //     'formula'         => 'GROSS_SALARY',
            //     'affects_net_pay' => false,
            // ],
            // [
            //     'code'            => 'TOTAL_DEDUCTION',
            //     'name'            => 'Total Deduction',
            //     'component_type'  => PayrollComponentTypes::DEDUCTION->value,
            //     'formula'         => 'TOTAL_DEDUCTION',
            //     'affects_net_pay' => false,
            // ],
            // [
            //     'code'            => 'NET_SALARY',
            //     'name'            => 'Net Salary',
            //     'component_type'  => PayrollComponentTypes::EARNING->value,
            //     'formula'         => 'NET_SALARY',
            //     'affects_net_pay' => false,
            // ],
        ];

        foreach ($components as $component) {
            PayrollComponent::create([
                'code'                 => $component['code'],
                'name'                 => $component['name'],
                'component_type'       => $component['component_type'],
                'component_scope_mode' => PayrollComponentScopeModes::SAME_AMOUNT_FOR_ALL->value,
                'calculation_type'     => PayrollComponentCalculationTypes::FORMULA->value,
                'formula'              => $component['formula'],
                'is_system_default'    => 1,
                'recurring'            => 1,
                'taxable'              => 1,
                'affects_net_pay'      => $component['affects_net_pay'],
                'is_active'            => 1,
            ]);
        }
    }
}
