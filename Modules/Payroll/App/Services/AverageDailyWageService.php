<?php
namespace Modules\Payroll\App\Services;

use Modules\Payroll\App\Models\AverageDailyWage;
use Modules\Payroll\App\Repositories\AverageDailyWageRepository;
use Modules\Payroll\App\Repositories\PayrollComponentRepository;

class AverageDailyWageService
{
     protected $repository;

    public function __construct(AverageDailyWageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get ADW by ID (with relationships).
     */
    public function findFirst(): ?AverageDailyWage
    {
        return $this->repository->findFirst();
    }

    /**
     * Create or update the single ADW record.
     */
    public function createOrUpdate(array $data): AverageDailyWage
    {
        return $this->repository->createOrUpdate($data);
    }
}