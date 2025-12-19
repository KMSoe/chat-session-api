<?php
namespace Database\Seeders;

use App\Models\SystemBuildInComponent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemBuildInComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system_build_in_components')->truncate();

        $components = [
            // Attendance Components
            [
                'code'        => 'ACTUAL_WORKING_DAYS',
                'name'        => 'Actual Working Days',
                'category'    => 'Attendance',
                'description' => 'Number of days employee worked in the period',
            ],
            [
                'code'        => 'TOTAL_WORKING_DAYS',
                'name'        => 'Total Working Days',
                'category'    => 'Attendance',
                'description' => 'Number of working days in the period (excluding holiday/weekend)',
            ],
            [
                'code'        => 'ACTUAL_WORKING_HOURS',
                'name'        => 'Actual Working Hours',
                'category'    => 'Attendance',
                'description' => 'Total approved working hours',
            ],
            [
                'code'        => 'TOTAL_WORKING_HOURS',
                'name'        => 'Total Working Hours',
                'category'    => 'Attendance',
                'description' => 'Expected standard working hours',
            ],
            [
                'code'        => 'ABSENT_COUNT',
                'name'        => 'Absent Count',
                'category'    => 'Attendance',
                'description' => 'Total number of unpaid absent days',
            ],
            [
                'code'        => 'UNPAID_LEAVE_COUNT',
                'name'        => 'Unpaid Leave Count',
                'category'    => 'Attendance',
                'description' => 'Total unpaid leave days',
            ],
            [
                'code'        => 'PAID_LEAVE_COUNT',
                'name'        => 'Paid Leave Count',
                'category'    => 'Attendance',
                'description' => 'Total paid leave days',
            ],
            [
                'code'        => 'LATE_COUNT',
                'name'        => 'Late Count',
                'category'    => 'Attendance',
                'description' => 'Number of late arrivals',
            ],
            [
                'code'        => 'LATE_MINUTES',
                'name'        => 'Late Minutes',
                'category'    => 'Attendance',
                'description' => 'late minutes',
            ],
            [
                'code'        => 'EARLY_LEAVE_COUNT',
                'name'        => 'Early Leave Count',
                'category'    => 'Attendance',
                'description' => 'Number of early leaves',
            ],
            [
                'code'        => 'EARLY_LEAVE_MINUTES',
                'name'        => 'Early Leave Minutes',
                'category'    => 'Attendance',
                'description' => 'Early Leave Minutes',
            ],
            [
                'code'        => 'OT_HOURS',
                'name'        => 'Overtime Hours',
                'category'    => 'Attendance',
                'description' => 'Total approved overtime hours',
            ],

            // Payroll Components
            [
                'code'        => 'BASIC_SALARY',
                'name'        => 'Basic Salary',
                'category'    => 'Payroll',
                'description' => 'The basic salary amount for the employee before any allowance or deductions',
            ],
            [
                'code'        => 'DAILY_RATE',
                'name'        => 'Daily Rate',
                'category'    => 'Payroll',
                'description' => 'Calculated daily rate from basic or gross salary',
            ],
            [
                'code'        => 'WEEKLY_RATE',
                'name'        => 'Weekly Rate',
                'category'    => 'Payroll',
                'description' => 'Calculated weekly rate from basic or gross salary',
            ],
            [
                'code'        => 'HOURLY_RATE',
                'name'        => 'Hourly Rate',
                'category'    => 'Payroll',
                'description' => 'Calculated hourly rate from basic or gross salary',
            ],
            [
                'code'        => 'MINUTE_RATE',
                'name'        => 'Minute Rate',
                'category'    => 'Payroll',
                'description' => 'Calculated Per-minute rate from basic or gross salary',
            ],
            [
                'code'        => 'OVERTIME_PAY',
                'name'        => 'Overtime Pay',
                'category'    => 'EARNING',
                'description' => 'Additional compensation for hours worked beyond the standard working schedule',
            ],
            [
                'code'        => 'LEAVE_PAY',
                'name'        => 'Leave Pay',
                'category'    => 'EARNING',
                'description' => 'Payment for earned leave days or encashment of unused leave balance',
            ],
            [
                'code'        => 'HOLIDAY_PAY',
                'name'        => 'Holiday Pay',
                'category'    => 'EARNING',
                'description' => 'Payment for Holiday',
            ],
            [
                'code'        => 'WEEK_OFF_PAY',
                'name'        => 'Week Off Pay',
                'category'    => 'EARNING',
                'description' => 'Payment for Week Off',
            ],
            [
                'code'        => 'LATE_MINUTES_DEDUCTION',
                'name'        => 'Late Minutes Deduction',
                'category'    => 'DEDUCTION',
                'description' => 'Salary reduction based on the total minutes late for scheduled shifts',
            ],
            [
                'code'        => 'EARLY_OUT_DEDUCTION',
                'name'        => 'Early Out Deduction',
                'category'    => 'DEDUCTION',
                'description' => 'Salary reduction for leaving the workplace before the scheduled end time',
            ],
            [
                'code'        => 'UNPAID_LEAVE_DEDUCTION',
                'name'        => 'Unpaid Leave Deduction',
                'category'    => 'DEDUCTION',
                'description' => 'Deduction for days taken as leave that exceed the paid leave entitlement',
            ],
            [
                'code'        => 'ABSENT_DEDUCTION',
                'name'        => 'Absent Deduction',
                'category'    => 'DEDUCTION',
                'description' => 'Salary reduction for unauthorized absence or missing work days without prior notice',
            ],
            [
                'code'        => 'MPF_EMPLOYEE',
                'name'        => 'MPF (Employee)',
                'category'    => 'Payroll',
                'description' => 'Statutory contribution by employee under Hong Kong MPF scheme',
            ],
            [
                'code'        => 'MPF_EMPLOYER',
                'name'        => 'MPF (Employer)',
                'category'    => 'Payroll',
                'description' => 'Statutory employer contribution under Hong Kong MPF scheme',
            ],
            [
                'code'        => 'ORSO_EMPLOYEE',
                'name'        => 'ORSO (Employee)',
                'category'    => 'Payroll',
                'description' => 'Employee contribution under ORSO scheme (Hong Kong)',
            ],
            [
                'code'        => 'ORSO_EMPLOYER',
                'name'        => 'ORSO (Employer)',
                'category'    => 'Payroll',
                'description' => 'Employer contribution under Hong Kong ORSO scheme, not deducted from net salary',
            ],
        ];

        foreach ($components as $component) {
            SystemBuildInComponent::firstOrCreate(
                ['code' => $component['code']],
                $component
            );
        }
    }
}
