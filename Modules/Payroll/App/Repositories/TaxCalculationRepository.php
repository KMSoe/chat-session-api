<?php
namespace Modules\Payroll\App\Repositories;

use Carbon\Carbon;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Modules\Payroll\App\Models\PayrollEntry;
use Modules\Payroll\App\Models\TaxCalculation;
use Modules\Payroll\App\resources\TaxCalculationResource;

class TaxCalculationRepository
{
    public function findByParams($params)
    {
        $search   = ! empty($params['search']) ? $params['search'] : '';
        $per_page = isset($params['per_page']) ? intval($params['per_page']) : 20;

        $query = TaxCalculation::with('employeeTaxCalculations.incomeDetails', 'taxForm');

        if (isset($params['status']) && $params['status'] !== 'All') {
            $query->where('status', $params['status']);
        }

        if ($search != '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('taxForm', function ($query) use ($search) {
                    $query->where('form_type', 'LIKE', "%$search%");
                });
            });
        }

        $data = $query->orderByDesc('created_at')->paginate($per_page);

        $items = $data->getCollection();

        $items = collect($items)->map(function ($item) {
            return new TaxCalculationResource($item);
        });

        $data = $data->setCollection($items);

        return $data;
    }

    public function findById($id)
    {
        return TaxCalculation::with('employeeTaxCalculations.incomeDetails')->findOrFail($id);
    }

    public function store(array $data)
    {
        $data['status'] = 'Draft';
        $calculation    = TaxCalculation::create($data);

        return $calculation;
    }

    public function updateStatus($id, $status)
    {
        $calculation         = TaxCalculation::findOrFail($id);
        $calculation->status = $status;
        $calculation->save();

        return $calculation;
    }

    public function recalculate($id)
    {
        // return EmployeeTaxCalculationIncomeDetail::join('employee_tax_calculations', 'employee_tax_calculations.id', 'employee_tax_calculation_income_details.employee_tax_calculation_id')
        //     ->where('employee_tax_calculations.is_locked', false)
        //     ->where('employee_tax_calculation_income_details.tax_calculation_id', $id)
        //     ->select('employee_tax_calculation_income_details.*')
        //     ->update([
        //         'amount' => 0,
        //     ]);

        $tax_calculation = TaxCalculation::with(['employeeTaxCalculations' => function ($query) {
            $query->with(['incomeDetails.taxFormIncomeCategory' => function ($subquery) {
                $subquery->with(['incomeAdditions', 'incomeDeductions']);
            }]);
        }])
            ->whereHas('employeeTaxCalculations', function ($query) {
                $query->where('is_locked', false);
            })
            ->firstOrFail();

        $period_type   = $tax_calculation->period_type;
        $payroll_month = null;
        $start_date    = null;
        $end_date      = null;

        if ($period_type == 'single') {
            $payroll_month = Carbon::parse($tax_calculation->date);
        } else {
            $start_date = Carbon::parse($tax_calculation->start_date);
            $end_date   = Carbon::parse($tax_calculation->end_date);
        }

        $payroll_entries = PayrollEntry::join('payrolls', 'payrolls.id', 'payroll_entries.payroll_id')
            ->whereIn('payrolls.employee_id', $tax_calculation->employeeTaxCalculations->pluck('employee_id')->toArray());

        if ($period_type == 'single') {
            $payroll_entries = $payroll_entries->whereMonth('payrolls.pay_date', $payroll_month->format('Y-m'));
        } else {
            $payroll_entries = $payroll_entries->whereBetween('payrolls.pay_date', [$start_date->format('Y-m-d'), $end_date->format('Y-m-d')]);
        }

        $payroll_entries = $payroll_entries->select('payrolls.employee_id', 'payroll_entries.*')->get();

        foreach ($tax_calculation->employeeTaxCalculations as $key => $employeeTaxCalculation) {
            foreach ($employeeTaxCalculation->incomeDetails as $key => $incomeDetail) {
                $income_additions_amount  = 0;
                $income_deductions_amount = 0;

                $incomeAdditions  = $incomeDetail->taxFormIncomeCategory->incomeAdditions;
                $incomeDeductions = $incomeDetail->taxFormIncomeCategory->incomeDeductions;

                foreach ($incomeAdditions as $key => $income_component) {
                    $payroll_earn_amount = collect($payroll_entries)->where('type', PayrollComponentTypes::EARNING->value)
                        ->where('employee_id', $employeeTaxCalculation->employee_id)
                        ->where('payroll_component_id', $income_component->id)
                        ->sum('amount');
                    $income_additions_amount += $payroll_earn_amount;
                }

                foreach ($incomeDeductions as $key => $deduct_component) {
                    $payroll_deduct_amount = collect($payroll_entries)->where('type', PayrollComponentTypes::EARNING->value)
                        ->where('employee_id', $employeeTaxCalculation->employee_id)
                        ->where('payroll_component_id', $deduct_component->id)
                        ->sum('amount');

                    $income_deductions_amount += $payroll_deduct_amount;
                }

                $incomeDetail->amount = $income_additions_amount - $income_deductions_amount;
                $incomeDetail->save();
            }
        }
    }

    public function delete($id)
    {
        $calculation = TaxCalculation::findOrFail($id);
        return $calculation->delete();
    }
}
