<?php
namespace App\Repositories;

use App\Filters\RoleFilter;
use App\Http\Resources\RoleResource;
use App\Models\CustomRole;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class RoleRepo
{
    /**
     * Get All Categories With Filter Value
     */
    function getAll(array $data, RoleFilter $filters)
    {
        $roles = CustomRole::filter($filters);
        if (isset($data['page']) && isset($data['per_page'])) {
            $roles = $roles->paginate($data['per_page']);
        } else {
            $roles = $roles->get();
        }
        return $roles;
    }

    /**
     * Get All Permissions.
     */
    public function all()
    {
        return Role::all();
    }

    /**
     * Get Permissions With Pagination.
     */
    public function paginate(array $data)
    {
        $perPage = $data['per_page'] ?? 20;

        $roles = CustomRole::query();

        if(isset($data['search'])) {
            $roles->where(function ($query) use ($data) {
                $query->where('name', 'like', '%' . $data['search'] . '%');
            });
        }

        if (isset($data['sort']) && $data['sort'] != null && $data['sort'] != '') {
            $sorts = explode(',', $data['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $roles->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $roles->orderBy('created_at', 'DESC');
        }

        if (isset($data['page']) && isset($data['per_page'])) {
            $roles = $roles->paginate($perPage);
        } else {
            $roles = $roles->get();
        }

        $data = $roles->getCollection()->map(function ($item) {
            return new RoleResource($item);
        });

        return $roles->setCollection($data);
    }

    /**
     * Get Permission with id
     */
    public function get($id)
    {
        return CustomRole::with(['assignedModules' => function ($moduleQuery) use ($id) {
            $moduleQuery->with(['assignedPermissions' => function ($permissionQuery) use ($id) {
                $permissionQuery->where('role_id', $id);
            }])
                ->groupBy('module_id');
        }])->findOrFail($id);
    }

    public function getUserRole()
    {
        $user = auth()->user();

        $roleId = $user->roles->first()->id ?? null;
        $role   = CustomRole::with(['assignedModules' => function ($moduleQuery) use ($roleId) {
            $moduleQuery->with(['assignedPermissions' => function ($permissionQuery) use ($roleId) {
                $permissionQuery->where('role_id', $roleId);
            }])
                ->groupBy('module_id');
        }])->findOrFail($roleId);

        $role->parent_modules = Module::whereNull('parent_module_id')->get();

        return $role;
    }

    public function getModulesAndPremissionsByRole($roleId)
    {
        return Module::with(['children.allPermissions' => function ($query) use ($roleId) {
            $query->leftJoin('role_module_permissions as rmp', function ($join) use ($roleId) {
                $join->on('rmp.permission_id', '=', 'permissions.id')
                    ->where('rmp.role_id', $roleId)
                    ->whereColumn('rmp.module_id', 'module_permissions.module_id');
            })
                ->select('permissions.*')
                ->addSelect(DB::raw('CASE WHEN rmp.id IS NULL THEN false ELSE true END as is_checked'));
        }])
            ->whereNull('parent_module_id')
            ->get();
    }

    /**
     * Create Permission
     */
    public function create(array $data)
    {
        $data['guard_name'] = 'api';
        $role               = Role::create($data);

        $role_module_permissions = [];

        foreach ($data['modules'] as $key => $module) {
            foreach ($module['children'] as $key => $child) {
                foreach ($child['permission_ids'] as $key => $permission_id) {
                    $role_module_permissions[] = [
                        'role_id'       => $role->id,
                        'permission_id' => $permission_id,
                        'module_id'     => $child['id'],
                    ];
                }

            }
        }

        DB::table('role_module_permissions')->insert($role_module_permissions);

        return $role;
    }

    /**
     * Update Permission
     */
    public function update($id, array $data)
    {
        $role               = Role::findOrFail($id);
        $data['guard_name'] = 'api';
        $role->update($data);

        DB::table('role_module_permissions')->where('role_id', $id)->delete();

        $role_module_permissions = [];

        foreach ($data['modules'] as $key => $module) {
            foreach ($module['children'] as $key => $child) {
                foreach ($child['permission_ids'] as $key => $permission_id) {
                    $role_module_permissions[] = [
                        'role_id'       => $role->id,
                        'permission_id' => $permission_id,
                        'module_id'     => $child['id'],
                    ];
                }

            }
        }

        DB::table('role_module_permissions')->insert($role_module_permissions);

        return $role;
    }

    /**
     * Delete Permission
     */
    public function delete($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids)
    {
        $roles = Role::whereIn('id', $ids)->get();
        foreach ($roles as $key => $role) {
            $role->delete();
        }
    }
}
