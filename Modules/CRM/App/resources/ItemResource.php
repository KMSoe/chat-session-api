<?php
namespace Modules\CRM\App\resources;

use App\Http\Services\CustomValuesService;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $customFields = $this->customValues?->filter(fn($cv) => $cv->attribute)->mapWithKeys(function ($cv) {
            $name  = $cv->attribute->name;
            $value = $cv->is_encrypted
                ? app(CustomValuesService::class)->decryptValue($cv->target_value)
                : $cv->target_value;
            return [$name => $value];
        })->toArray();

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'rate'      => $this->amount,
            'item_type'   => $this->itemType,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            ...$customFields,
        ];
    }
}
