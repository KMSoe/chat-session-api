<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Employee\App\Models\Employee;
use Modules\Payroll\App\Models\EmployeePayrollComponent;
use Modules\Payroll\App\resources\EmployeePayrollComponentResource;

class EmployeePayrollComponentRepository
{
    public function findByParams($params = [])
    {
        $per_page = isset($params['per_page']) ? intval($params['per_page']) : 20;
        $search   = ! empty($params['search']) ? $params['search'] : '';

        $department_ids = collect(explode(',', $params['departments'] ?? ''))
            ->filter(fn($id) => ! empty($id))
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $data = Employee::with(['departments', 'payrollComponents' => function ($query) {
            $query->with(['component'])
                ->orderBy('payroll_component_id');
        }])
            ->where(function ($query) use ($params, $department_ids, $search) {
                if (count($department_ids) > 0 && strtolower($params['departments'] ?? '') !== 'all') {
                    $query->whereHas('departments', function ($q) use ($department_ids) {
                        $q->whereIn('departments.id', $department_ids);
                    });
                }

                if ($search != '') {
                    $query->where('name', 'LIKE', "%$search%")
                        ->orWhere('employee_code', 'LIKE', "%$search%");
                }
            });

        if (! empty($params['export']) && $params['export']) {
            if (! empty($params['only_this_page']) && $params['only_this_page']) {
                $data = $data->skip((($params['page'] ?? 1) - 1) * $per_page)->take($per_page)->get();
            } else {
                $data = $data->get();
            }
        } else {
            $data = $data->paginate($per_page);

            $items = $data->getCollection();

            $items = collect($items)->map(function ($item) {
                return new EmployeePayrollComponentResource($item);
            });

            $data = $data->setCollection($items);
        }

        return $data;
    }

    public function updateEmployeePayrollComponents(array $payload)
    {
        $data = $payload['data'];

        foreach ($data as $item) {
            EmployeePayrollComponent::updateOrCreate(
                [
                    'employee_id'          => $item['employee_id'],
                    'payroll_component_id' => $item['payroll_component_id'],
                ],
                [
                    'amount' => $item['amount'],
                ]
            );
        }

        return true;
    }
}
