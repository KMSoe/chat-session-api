<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\EmployeeTaxFileRepository;

class EmployeeTaxFileService
{
    protected $repository;

    public function __construct(EmployeeTaxFileRepository $repository)
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

    public function findFilingRecordsByEmployeeId($employeeId, $params)
    {
        return $this->repository->findFilingRecordsByEmployeeId($employeeId, $params);
    }

    public function saveOrUpdate(array $data, $employeeId)
    {
        return $this->repository->save($data, $employeeId);
    }
}
