<?php

namespace App\Trait;

use Modules\Employee\App\Models\Department;
use Modules\Employee\App\Models\Employee;
use Modules\Employee\App\Models\Group;

trait HasRoleModulePermission
{
    public function canAccess(string $moduleName, string $permissionName): bool
    {
        return auth()->check() && auth()->user()->hasPermission($moduleName, $permissionName);
    }
}
