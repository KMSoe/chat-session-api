<?php
namespace App\Repositories;

use App\Http\Resources\ModuleResource;
use App\Models\Attribute;
use App\Models\CodePrefix;
use App\Models\CustomPermission;
use App\Models\CustomRole;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModuleRepo
{
    /**
     * Get All Modules.
     */
    public function getAll()
    {
        return Module::all();
    }

    /**
     * Get Modules With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;
        $modules = Module::query();

        // Filter by search
        if (isset($request['search'])) {
            $modules->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['search'] . '%');
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $modules->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $modules->orderBy('created_at', 'DESC');
        }

        $modules = $modules->paginate($perPage);

        $data = $modules->getCollection()->map(function ($item) {
            return new ModuleResource($item);
        });

        return $modules->setCollection($data);
    }

    /**
     * Get Module with id
     */
    public function get($id)
    {
        return Module::findOrFail($id);
    }

    /**
     * Create Module
     */
    public function create(array $data)
    {
        $data['parent_module_id'] = null;

        if (isset($data['parent_module'])) {
            $data['parent_module_id'] = Module::where('name', $data['parent_module'])->value('id') ?? null;
        }

        $module = Module::updateOrCreate([
            'name' => $data['name'],
        ],
            $data);
        $permissions = $data['permissions'] ?? [];

        if (count($permissions)) {
            $module_permissions = collect($permissions)->map(function ($permission_name) use ($module) {
                $permission = CustomPermission::where('name', $permission_name)->first();

                if ($permission) {
                    return [
                        'permission_id' => $permission->id,
                        'module_id'     => $module->id,
                    ];
                }

            });

            DB::table('module_permissions')->where('module_id', $module->id)->delete();
            DB::table('module_permissions')->insert($module_permissions->toArray());

            $adminRole = CustomRole::firstOrCreate(['name' => "Super Admin", 'guard_name' => 'api']);

            $role_module_permissions = collect($module_permissions)->map(function ($module_permission) use ($adminRole) {
                return [
                    'role_id'       => $adminRole->id,
                    'permission_id' => $module_permission['permission_id'],
                    'module_id'     => $module_permission['module_id'],
                ];
            });

            DB::table('role_module_permissions')
                ->where('module_id', $module->id)
                ->where('role_id', $adminRole->id)
                ->delete();
            DB::table('role_module_permissions')->insert($role_module_permissions->toArray());
        }

        return $module;
    }

    /**
     * Update Module
     */
    public function update($id, array $data)
    {
        $module = Module::findOrFail($id);

        $data['parent_module_id'] = null;

        if (isset($data['parent_module'])) {
            $data['parent_module_id'] = Module::where('name', $data['parent_module'])->value('id') ?? null;
        }

        $module->update($data);

        $permissions = $data['permissions'] ?? [];

        if (count($permissions)) {
            $module_permissions = collect($permissions)->map(function ($permission_name) use ($module) {
                $permission = CustomPermission::where('name', $permission_name)->first();

                if ($permission) {
                    return [
                        'permission_id' => $permission->id,
                        'module_id'     => $module->id,
                    ];
                }

            });

            DB::table('module_permissions')->where('module_id', $module->id)->delete();
            DB::table('module_permissions')->insert($module_permissions->toArray());

            $adminRole = CustomRole::firstOrCreate(['name' => "Super Admin", 'guard_name' => 'api']);

            $role_module_permissions = collect($module_permissions)->map(function ($module_permission) use ($adminRole) {
                return [
                    'role_id'       => $adminRole->id,
                    'permission_id' => $module_permission['permission_id'],
                    'module_id'     => $module_permission['module_id'],
                ];
            });

            DB::table('role_module_permissions')
                ->where('module_id', $module->id)
                ->where('role_id', $adminRole->id)
                ->delete();

            DB::table('role_module_permissions')->insert($role_module_permissions->toArray());
        }

        return $module;
    }

    /**
     * Delete Module
     */
    public function delete($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids)
    {
        $modules = Module::whereIn('id', $ids)->get();
        foreach ($modules as $key => $module) {
            $module->delete();
        }
    }

    /**
     * Get Associated Attributes for a Module.
     */
    public function getAssociatedAttributes($moduleId)
    {
        $module = Module::findOrFail($moduleId);
        return $module->attributes()->get();
    }

    /**
     * Assign Attributes to a Module.
     */
    public function assignAssociatedAttributes($moduleId, array $attributeIds)
    {
        $module     = Module::findOrFail($moduleId);
        $attributes = Attribute::whereIn('id', $attributeIds)->get();
        $module->attributes()->sync($attributes->pluck('id'));
        return $module->attributes()->get();
    }

    /**
     * Get Code Prefix for a Module.
     */    
    public function getCodePrefix($module)
    {
        $prefix = CodePrefix::where('module_name', $module)->firstOrFail();

        return $prefix;
    }

    public function updateCodePrefix($module, $data)
    {
        $prefix = CodePrefix::where('module_name', $module)->firstOrFail();
        $prefix->update($data);

        return $prefix;
    }
}
