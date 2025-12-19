<?php

namespace App\Trait;

use Modules\Employee\App\Models\Department;
use Modules\Employee\App\Models\Employee;
use Modules\Employee\App\Models\Group;

trait HasApplicableScope
{
    /**
     * Check if this record applies to an employee
     */
    public function appliesToEmployee(Employee $employee): bool
    {
        $employeeId     = $employee->id;
        $designationIds = $employee->designations()->pluck('designations.id')->toArray();
        $departmentIds  = $employee->departments()->pluck('departments.id')->toArray();
        $groupIds       = $employee->groups()->pluck('groups.id')->toArray();

        // Expand hierarchy
        $departmentIds = array_unique(array_merge(
            $departmentIds, 
            static::expandDepartmentAncestors($departmentIds),
            // static::expandDepartmentDescendants($departmentIds)
        ));
        $groupIds = array_unique(array_merge(
            $groupIds, 
            static::expandGroupAncestors($groupIds),
            // static::expandGroupDescendants($groupIds)
        ));

        return isset($this->applicableTos) ? $this->applicableTos()
            ->where(function ($q) use ($employeeId, $designationIds, $departmentIds, $groupIds) {
                $q->where(function ($q) use ($employeeId) {
                    $q->where('scope', 'employee')->where('target_id', $employeeId);
                });
                
                if (!empty($designationIds)) {
                    $q->orWhere(function ($q) use ($designationIds) {
                        $q->where('scope', 'designation')->whereIn('target_id', $designationIds);
                    });
                }
                
                if (!empty($departmentIds)) {
                    $q->orWhere(function ($q) use ($departmentIds) {
                        $q->where('scope', 'department')->whereIn('target_id', $departmentIds);
                    });
                }
                
                if (!empty($groupIds)) {
                    $q->orWhere(function ($q) use ($groupIds) {
                        $q->where('scope', 'group')->whereIn('target_id', $groupIds);
                    });
                }
            })
            ->exists() : true;
    }

    /**
     * Query scope to get all records applicable to an employee
     */
    public function scopeForEmployee($query, Employee $employee)
    {
        $employeeId     = $employee->id;
        $designationIds = $employee->designations()->pluck('designations.id')->toArray();
        $departmentIds  = $employee->departments()->pluck('departments.id')->toArray();
        $groupIds       = $employee->groups()->pluck('groups.id')->toArray();

        // Expand hierarchy
        $departmentIds = array_unique(array_merge(
            $departmentIds, 
            static::expandDepartmentAncestors($departmentIds),
            // static::expandDepartmentDescendants($departmentIds)
        ));
        $groupIds = array_unique(array_merge(
            $groupIds, 
            static::expandGroupAncestors($groupIds),
            // static::expandGroupDescendants($groupIds)
        ));

        return $query->whereHas('applicableTos', function ($q2) use ($employeeId, $designationIds, $departmentIds, $groupIds) 
        {
            $q2->where(function ($q) use ($employeeId, $designationIds, $departmentIds, $groupIds) {
                $q->where(function ($sub) use ($employeeId) {
                    $sub->where('scope', 'employee')->where('target_id', $employeeId);
                });
                
                if (!empty($designationIds)) {
                    $q->orWhere(function ($sub) use ($designationIds) {
                        $sub->where('scope', 'designation')->whereIn('target_id', $designationIds);
                    });
                }
                
                if (!empty($departmentIds)) {
                    $q->orWhere(function ($sub) use ($departmentIds) {
                        $sub->where('scope', 'department')->whereIn('target_id', $departmentIds);
                    });
                }
                
                if (!empty($groupIds)) {
                    $q->orWhere(function ($sub) use ($groupIds) {
                        $sub->where('scope', 'group')->whereIn('target_id', $groupIds);
                    });
                }
            });
        });
    }

    /**
     * Get all parent groups going up the hierarchy
     */
    protected function expandGroupAncestors(array $groupIds): array
    {
        $all = collect($groupIds);
        $queue = collect($groupIds);

        while ($queue->isNotEmpty()) {
            // Get parent groups
            $parents = Group::whereIn('id', $queue->all())
                ->whereNotNull('parent_group_id')
                ->pluck('parent_group_id');
            
            $new = $parents->diff($all);
            if ($new->isEmpty()) break;
            
            $all = $all->merge($new);
            $queue = $new;
        }
        
        return $all->all();
    }

    /**
     * Get all child groups going down the hierarchy
     */
    protected function expandGroupDescendants(array $groupIds): array
    {
        $all = collect($groupIds);
        $queue = collect($groupIds);

        while ($queue->isNotEmpty()) {
            $children = Group::whereIn('parent_group_id', $queue->all())->pluck('id');
            $new = $children->diff($all);
            if ($new->isEmpty()) break;
            $all = $all->merge($new);
            $queue = $new;
        }
        return $all->all();
    }

    /**
     * Get all parent departments going up the hierarchy
     */
    protected function expandDepartmentAncestors(array $deptIds): array
    {
        $all = collect($deptIds);
        $queue = collect($deptIds);

        while ($queue->isNotEmpty()) {
            // Get parent departments
            $parents = Department::whereIn('id', $queue->all())
                ->whereNotNull('parent_department_id')
                ->pluck('parent_department_id');
            
            $new = $parents->diff($all);
            if ($new->isEmpty()) break;
            
            $all = $all->merge($new);
            $queue = $new;
        }
        
        return $all->all();
    }

    /**
     * Get all child departments going down the hierarchy
     */
    protected function expandDepartmentDescendants(array $deptIds): array
    {
        $all = collect($deptIds);
        $queue = collect($deptIds);

        while ($queue->isNotEmpty()) {
            $children = Department::whereIn('parent_department_id', $queue->all())->pluck('id');
            $new = $children->diff($all);
            if ($new->isEmpty()) break;
            $all = $all->merge($new);
            $queue = $new;
        }
        return $all->all();
    }
}
