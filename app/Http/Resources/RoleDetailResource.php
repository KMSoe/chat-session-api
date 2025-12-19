<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $modules = collect($this->modules)->map(function ($module) {
            return [
                'id'       => $module->id,
                'name'     => $module->name,
                'children' => $module->children->map(function ($child) {
                    return [
                        'id'          => $child->id,
                        'name'        => $child->name,
                        'permissions' => $child->allPermissions->map(function ($permission) {
                            return [
                                'id'         => $permission->id,
                                'name'       => $permission->name,
                                'is_checked' => $permission->is_checked ? true : false,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'label'   => $this->label,
            'modules' => $modules,
        ];
    }
}
