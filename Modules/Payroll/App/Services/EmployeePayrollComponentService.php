<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\EmployeePayrollComponentRepository;

class EmployeePayrollComponentService
{
    protected $repo;

    public function __construct(EmployeePayrollComponentRepository $repo)
    {
        $this->repo = $repo;
    }

    public function findByParams($params)
    {
        return $this->repo->findByParams($params);
    }

    public function updateEmployeePayrollComponents(array $payload)
    {
        return $this->repo->updateEmployeePayrollComponents($payload);
    }
}
