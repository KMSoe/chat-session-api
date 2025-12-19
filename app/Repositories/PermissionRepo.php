<?php

namespace App\Repositories;

use App\Filters\PermissionFilter;
use App\Models\CustomPermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRepo
{
    /**
     * Get All Categories With Filter Value
     */
    function getAll(array $data, PermissionFilter $filters) {
        $permissions = CustomPermission::filter($filters);

        if (isset($data['page']) && isset($data['per_page'])) {
            $permissions = $permissions->paginate($data['per_page']);
        } else {
            $permissions = $permissions->get();
        }
        return $permissions;
    }
    
    /**
     * Get All Permissions.
     */
    public function all()
    {
        return Permission::all();
    }

    /**
     * Get Permissions With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request->per_page ?? 10;
        $permissions = Permission::paginate($perPage);
        return $permissions;
    }

    /**
     * Get Permission with id
     */
    public function get($id)
    {
        return Permission::findOrFail($id);
    }

    /**
     * Create Permission
     */
    public function create(array $data)
    {
        $data['guard_name'] = 'web';
        $permission = Permission::create($data);
        return $permission;
    }

    /**
     * Update Permission
     */
    public function update($id, array $data)
    {
        $permission = Permission::findOrFail($id);
        $data['guard_name'] = 'web';
        $permission->update($data);

        return $permission;
    }

    /**
     * Delete Permission
     */
    public function delete($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
    }

    /**
     * Update Permission
     */
    public function updateStatus($id, array $data)
    {
        $permission = Permission::findOrFail($id);
        $role = Role::find($data['roleID']);

        if ($data['status']) {
            $permission->assignRole($role);
        } else {
            $role->revokePermissionTo($permission);
        }

        return $permission;
    }
}
