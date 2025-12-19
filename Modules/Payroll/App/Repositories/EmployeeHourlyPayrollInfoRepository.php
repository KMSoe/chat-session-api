<?php
namespace Modules\Payroll\App\Repositories;

class EmployeeHourlyPayrollInfoRepository
{

    public function getPerHourRate($employee)
    {
        return $employee->hourly_pay_rate;
    }

    public function getPerMinuteRate($employee)
    {
        return $employee->hourly_pay_rate / 60;
    }
}
