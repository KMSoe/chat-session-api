<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Attendance\Services\PayrollAttendanceService;

class EmployeeDailyPayrollInfoRepository
{
    public function getDailyRate($employee)
    {
        return $employee->daily_pay_rate;
    }

    public function getPerHourRate($employee, $dates = [])
    {
        $result = 0;

        $workingDays        = app(PayrollAttendanceService::class)->getTotalWorkingDaysWithinDates($employee, $dates);
        $workingHoursPerDay = app(PayrollAttendanceService::class)->getTotalWorkingHoursWithinDates($employee, $dates) / $workingDays;

        if ($workingDays > 0 && $workingHoursPerDay > 0) {
            $result = $employee->weekly_pay_rate / $workingHoursPerDay;
        }

        return $result;
    }

    public function getPerMinuteRate($employee, $dates = [])
    {
        $result = 0;

        $workingDays        = app(PayrollAttendanceService::class)->getTotalWorkingDaysWithinDates($employee, $dates);
        $workingHoursPerDay = app(PayrollAttendanceService::class)->getTotalWorkingHoursWithinDates($employee, $dates) / $workingDays;

        if ($workingDays > 0 && $workingHoursPerDay > 0) {
            $result = $employee->weekly_pay_rate / ($workingHoursPerDay * 60);
        }

        return $result;
    }
}
