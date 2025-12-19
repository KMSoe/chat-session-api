<?php
namespace App\Models;

use App\Filters\Filterable;
use Spatie\Permission\Models\Role;

class CustomRole extends Role
{
    use Filterable;

    public $guard_name = 'api';

    public function assignedModules()
    {
        return $this->belongsToMany(Module::class, 'role_module_permissions', 'role_id', 'module_id');
    }

    public function modulePermissions()
    {
        return $this->hasMany(RoleModulePermission::class, 'role_id');
    }

    // public function assignedPermissions()
    // {
    //     return $this->belongsToMany(Per::class, 'role_module_permissions', 'role_id', 'module_id');
    // }
}
