<?php

namespace App\Http\Services;

use Modules\Employee\App\Models\Department;
use Modules\Employee\App\Models\Designation;
use Modules\Employee\App\Models\Employee;
use Modules\Employee\App\Models\Group;

class ApplicableToDuplicateCheckService
{
    public function checkDuplicatesAllRecords(string $modelClass, array $newApplicableTos, string $context, $extra = null, $excludeId = null): array 
    {
        $existing = $modelClass::query();

        if ($context === 'leave' && $extra) {
            // For leave context, exclude by parent LeaveTypeConfiguration ID
            $existing->whereHas('leaveTypeConfiguration', function ($query) use ($extra, $excludeId) {
                $query->where('leave_type_id', $extra);
                
                // Exclude the parent record being updated
                if ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                }
            });
        } elseif ($context === 'overtime') {
            // For overtime context, exclude by parent Overtime ID
            if ($excludeId) {
                $existing->whereHas('overtimeSetting', function ($query) use ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                });
            }
        } elseif ($context === 'late_request') {
            // For late request context, exclude by parent LateRequest ID
            if ($excludeId) {
                $existing->whereHas('lateRequest', function ($query) use ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                });
            }
        } elseif ($context === 'leave_request') {
            // For leave request context, exclude by parent LeaveRequest ID
            if ($excludeId) {
                $existing->whereHas('leaveRequest', function ($query) use ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                });
            }
        } elseif ($context === 'payroll_slip') {
            // For payroll slip context, exclude by parent PayrollSlip ID
            if ($excludeId) {
                $existing->whereHas('payrollSlipTemplate', function ($query) use ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                });
            }
        } elseif ($context === 'approval_flow') {
            // For approval flow context, exclude by parent ApprovalFlow ID
            if ($excludeId) {
                $existing->whereHas('approvalFlow', function ($query) use ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                });
            }
        }
        // Add other contexts as needed

        $existing = $existing->get();

        $allApplicable = $existing->map(fn($row) => [
            'scope' => $row->scope,
            'ids' => [$row->target_id],
            'include_children' => $row->include_children ?? true,
        ])->toArray();

        // Get employees from existing records
        $existingEmployees = $this->expandEmployees($allApplicable);
        
        // Get employees from new data
        $newEmployees = $this->expandEmployees($newApplicableTos);

        if ($context === 'leave') {
            // Create composite keys for existing records
            $existingComposite = collect($existingEmployees)->map(fn($id) => $id . '-' . $extra);
            
            // Create composite keys for new records
            $newComposite = collect($newEmployees)->map(fn($id) => $id . '-' . $extra);
            
            // Find duplicates between existing and new
            $duplicates = $existingComposite->intersect($newComposite);
            
            $duplicateIds = $duplicates->map(fn($v) => explode('-', $v)[0])
                ->unique()
                ->toArray();
                
            // Convert employee IDs to names
            return Employee::whereIn('id', $duplicateIds)->pluck('name')->toArray();
        }

        // For other contexts, check duplicates between existing and new employees
        $duplicates = collect($existingEmployees)->intersect($newEmployees);
        
        $duplicateIds = $duplicates->unique()->toArray();
        
        // Convert employee IDs to names
        return Employee::whereIn('id', $duplicateIds)->pluck('name')->toArray();
    }

    public function expandEmployees(array $applicableTo): array
    {
        $employeeIds = [];

        foreach ($applicableTo as $item) {
            switch ($item['scope']) {
                case 'group':
                    $employeeIds = array_merge(
                        $employeeIds,
                        Group::whereIn('id', $item['ids'])
                            ->with('employees')
                            ->get()
                            ->pluck('employees.*.id')
                            ->flatten()
                            ->toArray()
                    );
                    break;

                case 'department':
                    $employeeIds = array_merge(
                        $employeeIds,
                        Department::whereIn('id', $item['ids'])
                            ->with('employees')
                            ->get()
                            ->pluck('employees.*.id')
                            ->flatten()
                            ->toArray()
                    );
                    break;

                case 'designation':
                    $employeeIds = array_merge(
                        $employeeIds,
                        Designation::whereIn('id', $item['ids'])
                            ->with('employees')
                            ->get()
                            ->pluck('employees.*.id')
                            ->flatten()
                            ->toArray()
                    );
                    break;

                case 'employee':
                    $employeeIds = array_merge($employeeIds, $item['ids']);
                    break;
            }
        }

        return $employeeIds;
    }
}
