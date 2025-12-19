<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Models\EmployeeEnrollment;
use Modules\Payroll\App\Repositories\EmployeeEnrollmentRepository;

class EmployeeEnrollmentService
{
    protected $repository;

    public function __construct(EmployeeEnrollmentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findByParams($params)
    {
        return $this->repository->findByParams($params);
    }

    public function findByEmployeeId($id)
    {
        return $this->repository->findByEmployeeId($id);
    }

    public function getMPFEmployee($employee, $gross_salary)
    {
        $enrollment = EmployeeEnrollment::with(['mpfScheme'])->where('employee_id', $employee->id)->first();

        $scheme_type = $enrollment->scheme_type;

        if ($scheme_type == 'MPF' && $enrollment?->mpfScheme == null) {
            return 0;
        }

        $minimum_income_level = $enrollment->scheme_type == 'MPF' ? $enrollment?->mpfScheme?->minimum_income_level ?? 0 : 0;
        $maximum_income_level = $enrollment->scheme_type == 'MPF' ? $enrollment?->mpfScheme?->maximum_income_level ?? 0 : 0;

        $contribution_based_on = $enrollment?->mpfScheme?->contribution_based_on;

        $base = 0;

        if ($contribution_based_on == 'Basic Salary') {
            $base = $employee->basic_salary;
            // if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
            //     $base = $employee->basic_salary;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
            //     $base = $employee->weekly_pay_rate;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::DAILY->value) {
            //     $base = $employee->daily_pay_rate;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::HOURLY->value) {
            //     $base = $employee->hourly_pay_rate;
            // }
        } else {
            $base = $gross_salary;
        }

        if ($base < $minimum_income_level) {
            return 0;
        }

        if ($base > $maximum_income_level) {
            $base = $maximum_income_level;
        }

        $employee_contribution_rate = $enrollment->scheme_type == 'MPF' ? $enrollment?->mpfScheme?->employee_contribution_rate ?? 0 : 0;
        $voluntary_rate_employee    = $enrollment->scheme_type == 'MPF' ? $enrollment?->mpfScheme?->voluntary_rate_employee ?? 0 : 0;

        return $base * (($employee_contribution_rate / 100) + ($voluntary_rate_employee / 100));
    }

    public function getMPFEmployer($employee, $gross_salary)
    {
        $enrollment = EmployeeEnrollment::with(['mpfScheme'])->where('employee_id', $employee->id)->first();

        $scheme_type = $enrollment->scheme_type;

        if ($scheme_type == 'MPF' && $enrollment?->mpfScheme == null) {
            return 0;
        }

        $minimum_income_level = $enrollment->scheme_type == 'MPF' ? $enrollment?->mpfScheme?->minimum_income_level ?? 0 : 0;
        $maximum_income_level = $enrollment->scheme_type == 'MPF' ? $enrollment?->mpfScheme?->maximum_income_level ?? 0 : 0;

        $contribution_based_on = $enrollment?->mpfScheme?->contribution_based_on;

        $base = 0;

        if ($contribution_based_on == 'Basic Salary') {
            $base = $employee->basic_salary;

            // if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
            //     $base = $employee->basic_salary;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
            //     $base = $employee->weekly_pay_rate;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::DAILY->value) {
            //     $base = $employee->daily_pay_rate;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::HOURLY->value) {
            //     $base = $employee->hourly_pay_rate;
            // }
        } else {
            $base = $gross_salary;
        }

        if ($base < $minimum_income_level) {
            return 0;
        }

        if ($base > $maximum_income_level) {
            $base = $maximum_income_level;
        }

        $employer_contribution_rate = $enrollment->scheme_type == 'MPF' ? $enrollment?->mpfScheme?->employer_contribution_rate ?? 0 : 0;
        $voluntary_rate_employer    = $enrollment->scheme_type == 'MPF' ? $enrollment?->mpfScheme?->voluntary_rate_employer ?? 0 : 0;

        return $base * (($employer_contribution_rate / 100) + ($voluntary_rate_employer / 100));
    }

    public function getORSOEmployee($employee, $gross_salary)
    {
        $enrollment = EmployeeEnrollment::with(['orsoScheme'])->where('employee_id', $employee->id)->first();

        $scheme_type = $enrollment->scheme_type;

        if ($scheme_type == 'ORSO' && $enrollment?->orsoScheme == null) {
            return 0;
        }

        $minimum_income_level = $enrollment->scheme_type == 'ORSO' ? $enrollment?->orsoScheme?->minimum_income_level ?? 0 : 0;
        $maximum_income_level = $enrollment->scheme_type == 'ORSO' ? $enrollment?->orsoScheme?->maximum_income_level ?? 0 : 0;

        $contribution_based_on = $enrollment?->orsoScheme?->contribution_based_on;

        $base = 0;

        if ($contribution_based_on == 'Basic Salary') {
            $base = $employee->basic_salary;

            // if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
            //     $base = $employee->basic_salary;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
            //     $base = $employee->weekly_pay_rate;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::DAILY->value) {
            //     $base = $employee->daily_pay_rate;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::HOURLY->value) {
            //     $base = $employee->hourly_pay_rate;
            // }
        } else {
            $base = $gross_salary;
        }

        if ($base < $minimum_income_level) {
            return 0;
        }

        if ($base > $maximum_income_level) {
            $base = $maximum_income_level;
        }

        $employee_contribution_rate = $enrollment->scheme_type == 'ORSO' ? $enrollment?->orsoScheme?->employee_contribution_rate ?? 0 : 0;
        $voluntary_rate_employee    = $enrollment->scheme_type == 'ORSO' ? $enrollment?->orsoScheme?->voluntary_rate_employee ?? 0 : 0;

        return $base * (($employee_contribution_rate / 100) + ($voluntary_rate_employee / 100));
    }

    public function getORSOEmployer($employee, $gross_salary)
    {
        $enrollment = EmployeeEnrollment::with(['orsoScheme'])->where('employee_id', $employee->id)->first();

        $scheme_type = $enrollment->scheme_type;

        if ($scheme_type == 'ORSO' && $enrollment?->orsoScheme == null) {
            return 0;
        }

        $minimum_income_level = $enrollment->scheme_type == 'ORSO' ? $enrollment?->orsoScheme?->minimum_income_level ?? 0 : 0;
        $maximum_income_level = $enrollment->scheme_type == 'ORSO' ? $enrollment?->orsoScheme?->maximum_income_level ?? 0 : 0;

        $contribution_based_on = $enrollment?->orsoScheme?->contribution_based_on;

        $base = 0;

        if ($contribution_based_on == 'Basic Salary') {
            $base = $employee->basic_salary;

            // if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
            //     $base = $employee->basic_salary;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
            //     $base = $employee->weekly_pay_rate;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::DAILY->value) {
            //     $base = $employee->daily_pay_rate;
            // } else if ($employee->payrollPolicy->pay_frequency == PayFrequencyTypes::HOURLY->value) {
            //     $base = $employee->hourly_pay_rate;
            // }
        } else {
            $base = $gross_salary;
        }

        if ($base < $minimum_income_level) {
            return 0;
        }

        if ($base > $maximum_income_level) {
            $base = $maximum_income_level;
        }

        $employer_contribution_rate = $enrollment->scheme_type == 'ORSO' ? $enrollment?->orsoScheme?->employer_contribution_rate ?? 0 : 0;
        $voluntary_rate_employer    = $enrollment->scheme_type == 'ORSO' ? $enrollment?->orsoScheme?->voluntary_rate_employer ?? 0 : 0;

        return $base * (($employer_contribution_rate ?? 0) + ($voluntary_rate_employer ?? 0));
    }

    public function saveOrUpdate(array $data, $employeeId)
    {
        return $this->repository->save($data, $employeeId);
    }
}
