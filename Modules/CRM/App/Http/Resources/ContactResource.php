<?php
namespace Modules\CRM\App\Http\Resources;

use App\Http\Services\CustomValuesService;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request3
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $customFields = $this->customValues?->filter(fn($cv) => $cv->attribute)->mapWithKeys(function ($cv) {
            $name = $cv->attribute->name;
            $value = $cv->is_encrypted
                ? app(CustomValuesService::class)->decryptValue($cv->target_value)
                : $cv->target_value;
            return [$name => $value];
        })->toArray();

        return [
            'id'           => $this->id,
            'contact_code' => $this->contact_code,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'email'        => $this->email,
            'work_phone_dial_code' => $this->work_phone_dial_code,
            'work_phone_number' => $this->work_phone_number,
            'mobile_phone_dial_code' => $this->mobile_phone_dial_code,
            'mobile_phone_number' => $this->mobile_phone_number,
            'is_customer'  => $this->is_customer,
            'is_primary'   => $this->is_primary,
            'status'       => $this->status,
            'company'      => $this->relationLoaded('company') ? new CompanyResource($this->company) : null,
            'created_by'   => $this->createdBy?->name,
            'updated_by'   => $this->updatedBy?->name,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            ...$customFields,
        ];
    }
}
