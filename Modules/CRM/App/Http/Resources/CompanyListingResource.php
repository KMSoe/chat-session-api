<?php
namespace Modules\CRM\App\Http\Resources;

use App\Http\Services\CustomValuesService;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyListingResource extends JsonResource
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
            'logo'         => $this->relationLoaded('logo') ? $this->logo?->path : null,
            'company_code' => $this->company_code,
            'name'         => $this->name,
            'domain'       => $this->domain,
            'email'        => $this->email,
            'phone' => $this->phone_dial_code . $this->phone_number,
            'currency_name' => $this->relationLoaded('currency') ? $this->currency?->name : null,
            'billing_country_name' => $this->relationLoaded('billingCountry') ? $this->billingCountry?->name : null,
            'billing_state_name' => $this->relationLoaded('billingState') ? $this->billingState?->name : null,
            'billing_district' => $this->billing_district,
            'shipping_country_name' => $this->relationLoaded('shippingCountry') ? $this->shippingCountry?->name : null,
            'shipping_state_name' => $this->relationLoaded('shippingState') ? $this->shippingState?->name : null,
            'shipping_district' => $this->shipping_district,
            'primary_contact' => $this->relationLoaded('contacts') ? $this->contacts->where('is_primary', true)->first()?->name : null,
            'status'       => $this->status,
            'created_by'  => $this->createdBy?->name,
            'updated_by'  => $this->updatedBy?->name,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            ...$customFields,
        ];
    }
}
