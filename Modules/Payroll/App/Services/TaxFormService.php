<?php
namespace Modules\Payroll\App\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Payroll\App\Models\TaxForm;
use Modules\Payroll\App\Repositories\TaxFormRepository;

class TaxFormService
{
    protected TaxFormRepository $taxFormRepository;

    public function __construct(TaxFormRepository $taxFormRepository)
    {
        $this->taxFormRepository = $taxFormRepository;
    }

    public function findAll($request)
    {
        return $this->taxFormRepository->findAll($request);
    }

    public function findById(int $id)
    {
        return $this->taxFormRepository->findById($id);
    }

    public function updateTaxFormStatus(int $id)
    {
        return $this->taxFormRepository->updateStatus($id);
    }

    public function getIncomeCategoryById(int $id)
    {
        return $this->taxFormRepository->getIncomeCategoryById($id);
    }

    public function updateCategoryComponents(int $categoryId, ?array $incomeAdditions, ?array $incomeDeductions)
    {
        return $this->taxFormRepository->updateCategoryComponents($categoryId, $incomeAdditions, $incomeDeductions);
    }

}
