<?php
namespace Modules\Payroll\App\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Employee\App\Models\Employee;
use Modules\Payroll\App\Models\EmployeeEnrollment;
use Modules\Payroll\App\resources\EmployeeEnrollmentResource;

class EmployeeEnrollmentRepository
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

        $query = Employee::with(['departments'])
            ->leftJoin('employee_enrollments', 'employee_enrollments.employee_id', 'employees.id')
            ->leftJoin('mpf_schemes as mpf', function ($join) {
                $join->on('mpf.id', '=', 'employee_enrollments.scheme_id')
                    ->where('employee_enrollments.scheme_type', '=', 'MPF');
            })
            ->leftJoin('orso_schemes as orso', function ($join) {
                $join->on('orso.id', '=', 'employee_enrollments.scheme_id')
                    ->where('employee_enrollments.scheme_type', '=', 'ORSO');
            })
            ->where(function ($query) use ($params, $department_ids, $search) {
                if (count($department_ids) > 0 && strtolower($params['departments'] ?? '') !== 'all') {
                    $query->whereHas('departments', function ($q) use ($department_ids) {
                        $q->whereIn('departments.id', $department_ids);
                    });
                }

                if ($search != '') {
                    $query->where('employees.name', 'LIKE', "%$search%")
                        ->orWhere('employees.employee_code', 'LIKE', "%$search%");
                }
            })
            ->select('employee_enrollments.*', 'employees.name', 'employees.id AS employee_id', 'employees.employee_code AS employee_code', 'employees.joined_date', DB::raw("COALESCE(mpf.name, orso.name) as scheme_name"));

        $data = $query->paginate($per_page);

        $items = $data->getCollection();

        $items = collect($items)->map(function ($item) {
            return new EmployeeEnrollmentResource($item);
        });

        $data = $data->setCollection($items);

        return $data;
    }

    public function findByEmployeeId($employeeId)
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return null;
        }

        return Employee::leftJoin('employee_enrollments', 'employee_enrollments.employee_id', 'employees.id')
            ->where('employees.id', $employeeId)
            ->leftJoin('mpf_schemes as mpf', function ($join) {
                $join->on('mpf.id', '=', 'employee_enrollments.scheme_id')
                    ->where('employee_enrollments.scheme_type', '=', 'MPF');
            })
            ->leftJoin('orso_schemes as orso', function ($join) {
                $join->on('orso.id', '=', 'employee_enrollments.scheme_id')
                    ->where('employee_enrollments.scheme_type', '=', 'ORSO');
            })
            ->select('employee_enrollments.*', 'employees.name', 'employees.id AS employee_id', 'employees.employee_code AS employee_code', 'employees.joined_date', DB::raw("COALESCE(mpf.name, orso.name) as scheme_name"))
            ->first();
    }

    public function save(array $data, $employeeId)
    {
        return EmployeeEnrollment::updateOrCreate(
            ['employee_id' => $employeeId],
            $data
        );
    }
}
