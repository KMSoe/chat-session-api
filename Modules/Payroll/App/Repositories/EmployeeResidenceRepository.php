<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Payroll\App\Models\EmployeeResidence;

class EmployeeResidenceRepository
{
    public function findByParams(array $params)
    {
        $per_page    = isset($params['per_page']) ? intval($params['per_page']) : 20;
        $employee_id = ! empty($params['employee_id']) ? $params['employee_id'] : 0;

        return EmployeeResidence::where(function ($query) use ($employee_id) {
            if ($employee_id) {
                $query->where('employee_id', $employee_id);
            }
        })
        ->orderByDesc('created_at')
        ->paginate($per_page);
    }

    public function findById($id)
    {
        return EmployeeResidence::findOrFail($id);
    }

    public function create(array $data)
    {
        return EmployeeResidence::create($data);
    }

    public function update($id, array $data)
    {
        $residence = $this->findById($id);
        $residence->update($data);
        return $residence;
    }

    public function delete($id)
    {
        $residence = $this->findById($id);
        return $residence->delete();
    }
}
