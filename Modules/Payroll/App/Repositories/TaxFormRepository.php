<?php
namespace Modules\Payroll\App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\App\Models\TaxForm;
use Modules\Payroll\App\Models\TaxFormIncomeCategory;

class TaxFormRepository
{
    public function findAll($request)
    {
        $search = $request->search ?? '';

        return TaxForm::where(function($query) use ($search) {
            if($search != '') {
                $query->where('form_type', 'LIKE', "%$search%");
            }
        })
        ->orderBy('sort_order')->get();
    }

    public function findById(int $id)
    {
        return TaxForm::with([
            'incomeCategories' => function ($query) {
                $query->with(['incomeAdditions:id,name', 'incomeDeductions:id,name']);
            },
        ])->findOrFail($id);
    }

    public function updateStatus(int $id)
    {
        $taxForm            = $this->findById($id);
        $taxForm->is_enable = ! $taxForm->is_enable;
        $taxForm->save();

        return $taxForm;
    }

    public function getIncomeCategoryById(int $id)
    {
        return TaxFormIncomeCategory::with(['incomeAdditions:id,name', 'incomeDeductions:id,name'])
            ->findOrFail($id);
    }

    public function updateCategoryComponents(int $categoryId, ?array $incomeAdditions, ?array $incomeDeductions)
    {
        DB::beginTransaction();

        $category = TaxFormIncomeCategory::findOrFail($categoryId);

        // Sync additions
        $category->incomeAdditions()->sync($incomeAdditions ?? []);

        // Sync deductions
        $category->incomeDeductions()->sync($incomeDeductions ?? []);

        DB::commit();
    }
}
