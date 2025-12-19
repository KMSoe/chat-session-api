<?php
namespace Modules\Payroll\App\Repositories;

use App\Http\Services\ApplicableToDuplicateCheckService;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\App\Helpers\PayrollHelper;
use Modules\Payroll\App\Models\EmployeePayrollSlipTemplate;
use Modules\Payroll\App\Models\PayrollSlipTemplate;
use Modules\Payroll\App\Models\PayrollSlipTemplateApplicableTo;
use Modules\Payroll\App\Models\PayrollSlipTemplateDeductionComponent;
use Modules\Payroll\App\Models\PayrollSlipTemplateEarningComponent;
use Modules\Payroll\App\resources\PayrollSlipTemplateResource;

class PayrollSlipTemplateRepository
{
    protected $duplicateCheckService;

    public function __construct(ApplicableToDuplicateCheckService $duplicateCheckService)
    {
        $this->duplicateCheckService = $duplicateCheckService;
    }

    public function findByParams($request)
    {
        $data = PayrollSlipTemplate::with(['applicableTos', 'earningComponents', 'deductionComponents'])
            ->where(function ($query) use ($request) {
                if ($request->search != '') {
                    $query->where('name', 'like', "%$request->search%");
                }
            })
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        $items = $data->getCollection();

        $items = collect($items)->map(function ($item) {
            return new PayrollSlipTemplateResource($item);
        });

        $data = $data->setCollection($items);

        return $data;
    }

    public function findById(int $id)
    {
        return PayrollSlipTemplate::with(['applicableTos', 'earningComponents', 'deductionComponents'])
            ->findOrFail($id);
    }

    public function store(array $data)
    {
        if (isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0) {
            $duplicates = $this->duplicateCheckService->checkDuplicatesAllRecords(
                PayrollSlipTemplateApplicableTo::class,
                $data['applicable_to'],
                'payroll_slip'
            );
            if (! empty($duplicates)) {
                // throw new \Exception('Duplicate employees: ' . implode(',', $duplicates));
                 abort(400, 'Duplicate employees: ' . implode(',', $duplicates));
            }
        }

        DB::beginTransaction();
        $payrollSlipTemplate = PayrollSlipTemplate::create($data);

        $this->syncComponents($payrollSlipTemplate, $data);

        if (isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0) {
            $this->createApplicableTo($payrollSlipTemplate, $data);

            $employee_ids = PayrollHelper::getEmployeeIdsFromApplicableTo($data['applicable_to']);

            $data = [];
            foreach ($employee_ids as $employee_id) {
                $data[] = [
                    'employee_id'              => $employee_id,
                    'payroll_slip_template_id' => $payrollSlipTemplate->id,
                ];
            }

            EmployeePayrollSlipTemplate::insert($data);
        }

        if ($payrollSlipTemplate->is_default) {
            PayrollSlipTemplate::whereNot('id', $payrollSlipTemplate->id)->update([
                'is_default' => false,
            ]);
        }

        DB::commit();

        return $payrollSlipTemplate;
    }

    public function update($id, array $data)
    {
        if (isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0) {
            $duplicates = $this->duplicateCheckService->checkDuplicatesAllRecords(
                PayrollSlipTemplateApplicableTo::class,
                $data['applicable_to'],
                'payroll_slip',
                null,
                $id
            );
            if (! empty($duplicates)) {
                // throw new \Exception('Duplicate employees: ' . implode(',', $duplicates));
                    abort(400, 'Duplicate employees: ' . implode(',', $duplicates));
            }
        }

        DB::beginTransaction();
        $payrollSlipTemplate = PayrollSlipTemplate::findOrFail($id);

        $payrollSlipTemplate->update($data);

        $payrollSlipTemplate->earningComponents()->delete();
        $payrollSlipTemplate->deductionComponents()->delete();

        $this->syncComponents($payrollSlipTemplate, $data);

        if (isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0) {
            $payrollSlipTemplate->applicableTos()->delete();
            $this->createApplicableTo($payrollSlipTemplate, $data);

            $employee_ids = PayrollHelper::getEmployeeIdsFromApplicableTo($data['applicable_to']);

            foreach ($employee_ids as $employee_id) {
                EmployeePayrollSlipTemplate::updateOrCreate(
                    [
                        'employee_id'              => $employee_id,
                        'payroll_slip_template_id' => $payrollSlipTemplate->id,
                    ]);
            }

            EmployeePayrollSlipTemplate::where('payroll_slip_template_id', $payrollSlipTemplate->id)
                ->whereNotIn('employee_id', $employee_ids)
                ->delete();
        }

        if ($data['is_default'] == true) {
            PayrollSlipTemplate::whereNot('id', $payrollSlipTemplate->id)->update([
                'is_default' => false,
            ]);
        }

        DB::commit();

        return true;
    }

    protected function syncComponents(PayrollSlipTemplate $template, array $data): void
    {
        if (! empty($data['earning_components'])) {
            foreach ($data['earning_components'] as $ec) {
                PayrollSlipTemplateEarningComponent::create([
                    'payroll_slip_template_id' => $template->id,
                    'payroll_component_id'     => $ec['payroll_component_id'],
                    'order'                    => $ec['order'] ?? 0,
                ]);
            }
        }

        if (! empty($data['deduction_components'])) {
            foreach ($data['deduction_components'] as $dc) {
                PayrollSlipTemplateDeductionComponent::create([
                    'payroll_slip_template_id' => $template->id,
                    'payroll_component_id'     => $dc['payroll_component_id'],
                    'order'                    => $dc['order'] ?? 0,
                ]);
            }
        }
    }

    public function createApplicableTo(PayrollSlipTemplate $payrollSlipTemplate, array $data)
    {
        $applicableTo = [];
        foreach ($data['applicable_to'] as $applicableToItem) {
            foreach ($applicableToItem['ids'] as $targetId) {
                $applicableTo[] = [
                    'payroll_slip_template_id' => $payrollSlipTemplate->id,
                    'scope'                    => $applicableToItem['scope'],
                    'target_id'                => $targetId,
                ];
            }
        }

        PayrollSlipTemplateApplicableTo::insert($applicableTo);
    }

    public function delete($id)
    {
        $payrollSlipTemplate = PayrollSlipTemplate::with(['applicableTos', 'earningComponents', 'deductionComponents'])
            ->findOrFail($id);

        $payrollSlipTemplate->applicableTos()->delete();
        $payrollSlipTemplate->earningComponents()->delete();
        $payrollSlipTemplate->deductionComponents()->delete();

        return $payrollSlipTemplate->delete();
    }

    public function bulkDelete(array $ids)
    {
        return PayrollSlipTemplate::whereIn('id', $ids)->delete();
    }

}
