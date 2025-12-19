<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Attendance\Services\PayrollAttendanceService;

class EmployeeMonthlyPayrollInfoRepository
{
    public function getDailyRate($employee, $paycycle_start_date, $paycycle_end_date)
    {
        $result              = 0;
        $monthlySalary       = $employee->basic_salary;
        $workingDaysPerMonth = app(PayrollAttendanceService::class)->getTotalWorkingDays($employee, $paycycle_start_date, $paycycle_end_date);

        if ($workingDaysPerMonth > 0) {
            $result = $monthlySalary / $workingDaysPerMonth;
        }

        return $result;
    }

    public function getPerHourRate($employee, $paycycle_start_date, $paycycle_end_date)
    {
        $result              = 0;
        $monthlySalary       = $employee->basic_salary;
        $workingDaysPerMonth = app(PayrollAttendanceService::class)->getTotalWorkingDays($employee, $paycycle_start_date, $paycycle_end_date);
        $workingHoursPerDay  = app(PayrollAttendanceService::class)->getTotalWorkingHours($employee, $paycycle_start_date, $paycycle_end_date) / $workingDaysPerMonth;

        if ($workingDaysPerMonth > 0 && $workingHoursPerDay > 0) {
            $result = $monthlySalary / ($workingDaysPerMonth * $workingHoursPerDay);
        }

        return $result;
    }

    public function getPerMinuteRate($employee, $paycycle_start_date, $paycycle_end_date)
    {
        $result              = 0;
        $monthlySalary       = $employee->basic_salary;
        $workingDaysPerMonth = app(PayrollAttendanceService::class)->getTotalWorkingDays($employee, $paycycle_start_date, $paycycle_end_date);
        $workingHoursPerDay  = app(PayrollAttendanceService::class)->getTotalWorkingHours($employee, $paycycle_start_date, $paycycle_end_date) / $workingDaysPerMonth;

        if ($workingDaysPerMonth > 0 && $workingHoursPerDay > 0) {
            $result = $monthlySalary / ($workingDaysPerMonth * $workingHoursPerDay * 60);
        }

        return $result;

    }

    
}
