<?php
namespace Modules\CRM\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "id"          => $this->id,
            "name"        => $this->name,
            "description" => $this->description,
            "attributes"  => $this->attributes,
            "created_at"  => $this->created_at,
            "updated_at"  => $this->updated_at,
        ];
    }
}
