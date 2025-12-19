<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\TaxCalculationRepository;

class TaxCalculationService
{
    protected $repo;

    public function __construct(TaxCalculationRepository $repo)
    {
        $this->repo = $repo;
    }

    public function findByParams($params)
    {
        return $this->repo->findByParams($params);
    }

    public function findById($id)
    {
        return $this->repo->findById($id);
    }

    public function store(array $data)
    {
        return $this->repo->store($data);
    }

    public function updateStatus($id, $status)
    {
        return $this->repo->updateStatus($id, $status);
    }

    public function recalculate($id)
    {
        return $this->repo->recalculate($id);
    }

    public function delete($id)
    {
        return $this->repo->delete($id);
    }
}