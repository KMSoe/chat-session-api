<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\EmployeeTaxCalculationRepository;

class EmployeeTaxCalculationService
{
    protected $repo;

    public function __construct(EmployeeTaxCalculationRepository $repo)
    {
        $this->repo = $repo;
    }

    public function findByParams($tax_calculation_id, $params)
    {
        return $this->repo->findByParams($tax_calculation_id, $params);
    }

    public function findByEmployeeId($tax_calculation_id, $employee_id)
    {
        return $this->repo->findByEmployeeId($tax_calculation_id, $employee_id);
    }

    public function store(array $data)
    {
        return $this->repo->store($data);
    }

    public function updateLockStatus($tax_calculation_id, $employee_id)
    {
        return $this->repo->updateLockStatus($tax_calculation_id, $employee_id);
    }

    public function getResidentialDetails($employee_id)
    {
        return $this->repo->getResidentialDetails($employee_id);
    }

    public function getIncomeDetails($tax_calculation_id, $employee_id)
    {
        return $this->repo->getIncomeDetails($tax_calculation_id, $employee_id);
    }

    public function updateIncomeDetails($tax_calculation_id, $employee_id, $data)
    {
        return $this->repo->updateIncomeDetails($tax_calculation_id, $employee_id, $data);
    }

    public function getOtherDetails($tax_calculation_id, $employee_id)
    {
        return $this->repo->getOtherDetails($tax_calculation_id, $employee_id);
    }

    public function updateOtherDetails($tax_calculation_id, $employee_id, $data)
    {
        return $this->repo->updateOtherDetails($tax_calculation_id, $employee_id, $data);
    }

    public function deleteByEmployeeId($tax_calculation_id, $employee_id)
    {
        return $this->repo->deleteByEmployeeId($tax_calculation_id, $employee_id);
    }

    public function bulkDdeleteByEmployeeIds($tax_calculation_id, $employee_ids)
    {
        return $this->repo->bulkDdeleteByEmployeeIds($tax_calculation_id, $employee_ids);
    }
}
