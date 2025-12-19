<?php
namespace Modules\Payroll\App\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Payroll\App\Models\EmployeeTaxCalculation;
use Modules\Payroll\App\Models\EmployeeTaxCalculationIncomeDetail;
use Modules\Payroll\App\Models\EmployeeTaxFile;
use Modules\Payroll\App\Models\TaxForm;
use Modules\Payroll\App\resources\EmployeeTaxCalculationResource;

class EmployeeTaxCalculationRepository
{
    public function findByParams($tax_calculation_id, $params)
    {
        $search   = ! empty($params['search']) ? $params['search'] : '';
        $per_page = isset($params['per_page']) ? intval($params['per_page']) : 20;

        $query = EmployeeTaxCalculation::with(['incomeDetails', 'taxCalculation', 'employee'])
            ->where('tax_calculation_id', $tax_calculation_id);

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                });
            });
        }

        $data = $query->orderByDesc('created_at')->paginate($per_page);

        $items = $data->getCollection();

        $items = collect($items)->map(function ($item) {
            return new EmployeeTaxCalculationResource($item);
        });

        $data = $data->setCollection($items);

        return $data;
    }

    public function findByEmployeeId($tax_calculation_id, $employee_id)
    {
        return EmployeeTaxCalculation::with(['incomeDetails', 'taxCalculation', 'employee'])
            ->where('tax_calculation_id', $tax_calculation_id)
            ->where('employee_id', $employee_id)
            ->firstOrFail();
    }

    public function store(array $data)
    {
        $tax_calculation_id = $data['tax_calculation_id'];
        $employee_ids       = $data['employee_ids'];
        $tax_form           = TaxForm::with(['incomeCategories'])
            ->join('tax_calculations', 'tax_calculations.tax_form_id', 'tax_forms.id')
            ->where('tax_calculations.id', $tax_calculation_id)
            ->select('tax_forms.*')
            ->firstOrFail();

        $income_categories = $tax_form->incomeCategories;

        foreach ($employee_ids as $key => $employee_id) {
            $employee_tax_calculation = EmployeeTaxCalculation::updateOrCreate([
                'tax_calculation_id' => $tax_calculation_id,
                'employee_id'        => $employee_id,
            ],
                [
                    'tax_calculation_id' => $tax_calculation_id,
                    'employee_id'        => $employee_id,
                ]);

            $income_details = [];
            foreach ($income_categories as $key => $income_category) {
                $income_details[] = [
                    'tax_calculation_id'          => $tax_calculation_id,
                    'employee_tax_calculation_id' => $employee_tax_calculation->id,
                    'tax_form_income_category_id' => $income_category->id,
                    'amount'                      => 0,
                ];
            }

            DB::table('employee_tax_calculation_income_details')->insert($income_details);
        }
    }

    public function updateLockStatus($tax_calculation_id, $employee_id)
    {
        $item = EmployeeTaxCalculation::where('tax_calculation_id', $tax_calculation_id)
            ->where('employee_id', $employee_id)->firstOrFail();

        $item->is_locked = ! $item->is_locked;
        $item->save();

        return $item;
    }

    public function getResidentialDetails($employee_id)
    {
        return EmployeeTaxFile::with(['residences'])
            ->where('employee_id', $employee_id)
            ->select('employee_id', 'employer_provides_residence')
            ->first();
    }

    public function getIncomeDetails($tax_calculation_id, $employee_id)
    {
        return EmployeeTaxCalculationIncomeDetail::with(['taxFormIncomeCategory', 'taxCalculation'])
            ->join('employee_tax_calculations', 'employee_tax_calculations.id', 'employee_tax_calculation_income_details.employee_tax_calculation_id')
            ->where('employee_tax_calculations.tax_calculation_id', $tax_calculation_id)
            ->where('employee_tax_calculations.employee_id', $employee_id)
            ->select('employee_tax_calculation_income_details.*')
            ->get();
    }

    public function updateIncomeDetails($tax_calculation_id, $employee_id, $data)
    {
        $idsInRequest             = collect($data['data'])->pluck('tax_form_income_category_id');
        $employee_tax_calculation = EmployeeTaxCalculation::where('tax_calculation_id', $tax_calculation_id)
            ->where('employee_id', $employee_id)
            ->firstOrFail();

        $employee_tax_calculation->incomeDetails()
            ->whereNotIn('tax_form_income_category_id', $idsInRequest)
            ->delete();

        foreach ($data['data'] as $row) {
            $employee_tax_calculation->incomeDetails()->updateOrCreate(
                [
                    'tax_form_income_category_id' => $row['tax_form_income_category_id'],
                ],
                [
                    'tax_calculation_id' => $tax_calculation_id,
                    'amount'             => $row['amount'],
                ]
            );
        }
    }
    public function getOtherDetails($tax_calculation_id, $employee_id)
    {
        return EmployeeTaxCalculation::where('tax_calculation_id', $tax_calculation_id)
            ->where('employee_id', $employee_id)
            ->select("tax_calculation_id", "employee_id", "wholly_or_partly_paid_either", "non_hong_kong_company_name", "address", "amount", "remarks")
            ->firstOrFail();
    }

    public function updateOtherDetails($tax_calculation_id, $employee_id, $data)
    {
        return EmployeeTaxCalculation::where('tax_calculation_id', $tax_calculation_id)
            ->where('employee_id', $employee_id)
            ->update($data);
    }

    public function deleteByEmployeeId($tax_calculation_id, $employee_id)
    {
        return EmployeeTaxCalculation::where('tax_calculation_id', $tax_calculation_id)
            ->where('employee_id', $employee_id)->delete();
    }

    public function bulkDdeleteByEmployeeIds($tax_calculation_id, $employee_ids)
    {
        return EmployeeTaxCalculation::where('tax_calculation_id', $tax_calculation_id)
            ->whereIn('employee_id', $employee_ids)->delete();
    }
}
