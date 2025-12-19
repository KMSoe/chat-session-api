<?php

namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'items'       => $this->relationLoaded('items') ? $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'rate' => $item->amount,
                ];
            }) : null,
            'created_by'  => $this->createdBy?->name,
            'updated_by'  => $this->updatedBy?->name,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
