<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\PayrollSlipTemplateRepository;

class PayrollSlipTemplateService
{
    protected PayrollSlipTemplateRepository $repository;

    public function __construct(PayrollSlipTemplateRepository $repository)
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

    public function store(array $data)
    {
        return $this->repository->store($data);
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
