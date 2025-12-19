<?php

namespace App\Http\Resources;

use App\Enums\AttributeType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'type_name' => AttributeType::tryFrom($this->type)?->label(),
            'is_required' => $this->is_required ? true : false,
            'status' => $this->status ? true : false,
            'options' => $this->options,
            'modules' => $this->modules,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}