<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\ORSOSchemeRepository;

class ORSOSchemeService
{
    protected $repository;

    public function __construct(ORSOSchemeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findByParams($request)
    {
        return $this->repository->findByParams($request);
    }

    public function get($id)
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

    public function bulkDelete(array $ids)
    {
        return $this->repository->bulkDelete($ids);
    }
}
