<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyRoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $parent_modules   = $this->parent_modules;
        $assigned_modules = $this->assignedModules;

        $modules = collect($parent_modules)->map(function ($parent_module) use ($assigned_modules) {
            $childTabs = $assigned_modules->filter(function ($module) use ($parent_module) {
                return $module->parent_module_id == $parent_module->id;
            });

            return [
                'id'        => $parent_module->id,
                'parentTab' => $parent_module->name,
                'childTabs' => ModuleWithPermissionResource::collection($childTabs),
            ];
        });

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'label'       => $this->label,
            'guard_name'  => $this->guard_name,
            'permissions' => $modules,
        ];
    }
}
