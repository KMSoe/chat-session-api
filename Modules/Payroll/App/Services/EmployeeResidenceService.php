<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\EmployeeResidenceRepository;

class EmployeeResidenceService
{
    protected $repository;

    public function __construct(EmployeeResidenceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findByParams(array $params)
    {
        return $this->repository->findByParams($params);
    }

    public function findById($id)
    {
        return $this->repository->findById($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
