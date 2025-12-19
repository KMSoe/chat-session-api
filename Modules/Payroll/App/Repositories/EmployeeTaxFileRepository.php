<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Employee\App\Models\Employee;
use Modules\Payroll\App\Models\EmployeeTaxCalculation;
use Modules\Payroll\App\Models\EmployeeTaxFile;
use Modules\Payroll\App\resources\EmployeeFilingRecordResource;
use Modules\Payroll\App\resources\EmployeeTaxFileResource;

class EmployeeTaxFileRepository
{
    public function findByParams($params)
    {
        $per_page = isset($params['per_page']) ? intval($params['per_page']) : 20;
        $search   = ! empty($params['search']) ? $params['search'] : '';

        $department_ids = collect(explode(',', $params['departments'] ?? ''))
            ->filter(fn($id) => ! empty($id))
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $data = Employee::with(['departments', 'designations'])
            ->leftJoin('employee_tax_files', 'employee_tax_files.employee_id', 'employees.id')
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
            })
            ->select('employee_tax_files.*',
                'employees.name', 'employees.id AS employee_id', 'employees.employee_code AS employee_code', 'employees.employment_type', 'employees.joined_date'
            );

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
                return new EmployeeTaxFileResource($item);
            });

            $data = $data->setCollection($items);
        }

        return $data;
    }

    public function findByEmployeeId($employeeId)
    {
        return Employee::with(['departments', 'designations'])
            ->leftJoin('employee_tax_files', 'employee_tax_files.employee_id', 'employees.id')
            ->where('employees.id', $employeeId)
            ->select('employee_tax_files.*',
                'employees.name', 'employees.id AS employee_id', 'employees.employee_code AS employee_code', 'employees.employment_type', 'employees.joined_date'
            )
            ->first();
    }

    public function findFilingRecordsByEmployeeId($employeeId, $params)
    {
        $per_page = isset($params['per_page']) ? intval($params['per_page']) : 20;

        $data = EmployeeTaxCalculation::with(['incomeDetails', 'taxCalculation.taxForm'])
            ->where('employee_id', $employeeId)
            ->whereHas('taxCalculation', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->paginate($per_page);

        $items = $data->getCollection();

        $items = collect($items)->map(function ($item) {
            return new EmployeeFilingRecordResource($item);
        });

        $data = $data->setCollection($items);

        return $data;
    }

    public function save(array $data, $employeeId)
    {
        return EmployeeTaxFile::updateOrCreate(
            ['employee_id' => $employeeId],
            $data
        );
    }
}
