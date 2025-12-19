<?php
namespace Modules\Payroll\App\Repositories;

use App\Models\SystemBuildInComponent;
use Illuminate\Support\Facades\DB;
use Modules\Employee\App\Models\Employee;
use Modules\Payroll\App\Helpers\PayrollHelper;
use Modules\Payroll\App\Models\EmployeePayrollComponent;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\Models\PayrollComponentApplicableTo;
use Modules\Payroll\App\Models\PayrollComponentCategory;
use Modules\Payroll\App\resources\PayrollComponentResource;

class PayrollComponentRepository
{
    public function findByParams($request)
    {
        $search   = $request->search ?? '';
        $per_page = intval($request->per_page) ?? 20;

        $category_ids = collect(explode(",", $request->categories))->filter(function ($category_id) {
            return $category_id;
        })->values();

        $data = PayrollComponent::with(['category', 'applicableTos'])
            ->where(function ($query) use ($request, $category_ids, $search) {
                if ($request->component_type != null && $request->component_type != 'all') {
                    $query->where('component_type', $request->component_type);
                }

                if (! empty($request->component_scope_mode) && strtolower($request->component_scope_mode) != 'all') {
                    $query->where('component_scope_mode', $request->component_scope_mode);
                }

                if (! empty($request->taxable)) {
                    $query->where('taxable', filter_var($$request->is_active, FILTER_VALIDATE_BOOLEAN));
                }

                if (! empty($request->is_active)) {
                    $query->where('is_active', filter_var($$request->is_active, FILTER_VALIDATE_BOOLEAN));
                }

                if (count($category_ids) > 0 && strtolower($request->categories) != 'all') {
                    $query->whereIn('payroll_component_category_id', $category_ids);
                }

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }
            })
            ->orderByDesc('created_at')
            ->paginate($per_page);

        $items = $data->getCollection();

        $items = collect($items)->map(function ($item) {
            return new PayrollComponentResource($item);
        });

        $data = $data->setCollection($items);

        return $data;
    }

    public function getPayrollComponentCategories()
    {
        return PayrollComponentCategory::all();
    }

    public function getSystemBuiltInComponents()
    {
        return SystemBuildInComponent::all();
    }

    public function findById(string $id): ?PayrollComponent
    {
        return PayrollComponent::with(['category', 'applicableTos'])->findOrFail($id);
    }

    public function findByCode(string $code): ?PayrollComponent
    {
        return PayrollComponent::where('code', $code)->first();
    }

    public function create(array $data): PayrollComponent
    {
        DB::beginTransaction();
        $data['effected_months'] = $data['recurring'] == false && isset($data['effected_months']) && is_array($data['effected_months']) ? $data['effected_months'] : [];
        $payroll_component       = PayrollComponent::create($data);

        if (isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0) {
            $this->createApplicableTo($payroll_component, $data);

            if ($payroll_component->component_scope_mode === 'Custom Amount per Employee') {
                $employee_ids = PayrollHelper::getEmployeeIdsFromApplicableTo($data['applicable_to']);
                $employees    = Employee::whereIn('id', $employee_ids)->get();

                foreach ($employees as $employee) {
                    EmployeePayrollComponent::create(
                        [
                            'employee_id'          => $employee->id,
                            'payroll_component_id' => $payroll_component->id,
                            'amount'               => 0,
                        ],
                    );
                }
            }
        }

        DB::commit();

        return $payroll_component;
    }

    public function update(string $id, array $data)
    {
        $payroll_component = PayrollComponent::findOrFail($id);

        DB::beginTransaction();

        $data['effected_months'] = $data['recurring'] == false && isset($data['effected_months']) && is_array($data['effected_months']) ? $data['effected_months'] : [];
        $payroll_component->update($data);

        if (isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0) {
            $payroll_component->applicableTos()->delete();
            $this->createApplicableTo($payroll_component, $data);

            if ($data['component_scope_mode'] === 'Custom Amount per Employee') {
                $employee_ids = PayrollHelper::getEmployeeIdsFromApplicableTo($data['applicable_to']);
                $employees    = Employee::whereIn('id', $employee_ids)->get();

                foreach ($employees as $employee) {
                    EmployeePayrollComponent::updateOrCreate(
                        [
                            'employee_id'          => $employee->id,
                            'payroll_component_id' => $payroll_component->id,
                        ],
                        [
                            // don’t overwrite existing amount if already set
                            'amount' => EmployeePayrollComponent::where('employee_id', $employee->id)
                                ->where('payroll_component_id', $payroll_component->id)
                                ->value('amount') ?? 0,
                        ]
                    );
                }

                EmployeePayrollComponent::where('payroll_component_id', $payroll_component->id)->whereNotIn('employee_id', $employees->pluck('id')->toArray())
                    ->delete();
            } else if ($data['component_scope_mode'] === 'Same Amount for All') {
                EmployeePayrollComponent::where('payroll_component_id', $payroll_component->id)
                    ->delete();
            }
        }

        DB::commit();

    }

    public function createApplicableTo(PayrollComponent $payroll_component, array $data)
    {
        $applicableTo = [];
        foreach ($data['applicable_to'] as $applicableToItem) {
            foreach ($applicableToItem['ids'] as $targetId) {
                $applicableTo[] = [
                    'payroll_component_id' => $payroll_component->id,
                    'scope'                => $applicableToItem['scope'],
                    'target_id'            => $targetId,
                ];
            }
        }

        PayrollComponentApplicableTo::insert($applicableTo);
    }

    public function delete(string $id): bool
    {
        return PayrollComponent::findOrFail($id)->delete();
    }
}
