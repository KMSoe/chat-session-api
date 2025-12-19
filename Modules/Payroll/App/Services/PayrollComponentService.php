<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\PayrollComponentRepository;

class PayrollComponentService
{
    public function __construct(
        private PayrollComponentRepository $repository
    ) {}

    public function findByParams($request)
    {
        return $this->repository->findByParams($request);
    }

    public function findById(string $id)
    {
        $component = $this->repository->findById($id);

        return $component;
    }

    public function getPayrollComponentCategories()
    {
        return $this->repository->getPayrollComponentCategories();
    }

    public function getSystemBuiltInComponents()
    {
        return $this->repository->getSystemBuiltInComponents();
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(string $id, array $data): void
    {
        $this->repository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
