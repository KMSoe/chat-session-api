<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\MPFTrusteeRepository;

class MPFTrusteeService
{
    protected $repository;

    public function __construct(MPFTrusteeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findByParams(array $params)
    {
        return $this->repository->findByParams($params);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }
}
