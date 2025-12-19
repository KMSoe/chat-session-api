<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Repositories\AdwOpeningBalanceRepository;

class AdwOpeningBalanceService
{
    protected $adwOpeningBalanceRepository;

    public function __construct(AdwOpeningBalanceRepository $adwOpeningBalanceRepository)
    {
        $this->adwOpeningBalanceRepository = $adwOpeningBalanceRepository;
    }

    public function findByParams(array $params)
    {
        return $this->adwOpeningBalanceRepository->findByParams($params);
    }

    public function findById(int $id)
    {
        return $this->adwOpeningBalanceRepository->findById($id);
    }

    public function create(array $data)
    {
        return $this->adwOpeningBalanceRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->adwOpeningBalanceRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->adwOpeningBalanceRepository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        return $this->adwOpeningBalanceRepository->bulkDelete($ids);
    }
}
