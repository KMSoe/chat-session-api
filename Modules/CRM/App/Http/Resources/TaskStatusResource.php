<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'project' => $this->project,
            'sort_order' => $this->sort_order,
            'category' => $this->category,
            'system_created' => $this->system_created,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
