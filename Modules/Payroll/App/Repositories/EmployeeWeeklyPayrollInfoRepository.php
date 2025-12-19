<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Attendance\Services\PayrollAttendanceService;

class EmployeeWeeklyPayrollInfoRepository
{
    public function getDailyRate($employee)
    {
        return $employee->weekly_pay_rate / 7;
    }

    public function getPerHourRate($employee, $paycycle_start_date, $paycycle_end_date)
    {
        $result = 0;

        $workingDays        = app(PayrollAttendanceService::class)->getTotalWorkingDays($employee, $paycycle_start_date, $paycycle_end_date);
        $workingHoursPerDay = app(PayrollAttendanceService::class)->getTotalWorkingHours($employee, $paycycle_start_date, $paycycle_end_date) / $workingDays;

        if ($workingDays > 0 && $workingHoursPerDay > 0) {
            $result = $employee->weekly_pay_rate / (7 * $workingHoursPerDay);
        }

        return $result;
    }

    public function getPerMinuteRate($employee, $paycycle_start_date, $paycycle_end_date)
    {
        $result = 0;

        $workingDays        = app(PayrollAttendanceService::class)->getTotalWorkingDays($employee, $paycycle_start_date, $paycycle_end_date);
        $workingHoursPerDay = app(PayrollAttendanceService::class)->getTotalWorkingHours($employee, $paycycle_start_date, $paycycle_end_date) / $workingDays;

        if ($workingDays > 0 && $workingHoursPerDay > 0) {
            $result = $employee->weekly_pay_rate / (7 * $workingHoursPerDay * 60);
        }

        return $result;
    }
}
