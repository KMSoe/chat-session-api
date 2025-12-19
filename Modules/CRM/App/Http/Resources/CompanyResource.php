<?php
namespace Modules\CRM\App\Http\Resources;

use App\Http\Services\CustomValuesService;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'phone_dial_code' => $this->phone_dial_code,
            'phone_number' => $this->phone_number,
            'tax'       => $this->relationLoaded('tax') ? $this->tax?->only(['id', 'name']) : null,
            'currency'  => $this->relationLoaded('currency') ? $this->currency?->only(['id', 'name']) : null,
            'billing_country' => $this->relationLoaded('billingCountry') ? $this->billingCountry?->only(['id', 'name']) : null,
            'billing_state' => $this->relationLoaded('billingState') ? $this->billingState?->only(['id', 'name']) : null,
            'billing_district' => $this->billing_district,
            'billing_zip_code' => $this->billing_zip_code,
            'billing_address_line_1' => $this->billing_address_line_1,
            'billing_address_line_2' => $this->billing_address_line_2,
            'billing_phone_dial_code' => $this->billing_phone_dial_code,
            'billing_phone_number' => $this->billing_phone_number,
            'shipping_same_as_billing' => $this->shipping_same_as_billing,
            'shipping_country' => $this->relationLoaded('shippingCountry') ? $this->shippingCountry?->only(['id', 'name']) : null,
            'shipping_state' => $this->relationLoaded('shippingState') ? $this->shippingState?->only(['id', 'name']) : null,
            'shipping_district' => $this->shipping_district,
            'shipping_zip_code' => $this->shipping_zip_code,
            'shipping_address_line_1' => $this->shipping_address_line_1,
            'shipping_address_line_2' => $this->shipping_address_line_2,
            'shipping_phone_dial_code' => $this->shipping_phone_dial_code,
            'shipping_phone_number' => $this->shipping_phone_number,
            'contacts' => $this->relationLoaded('contacts') 
                ? $this->contacts->map(function ($contact) {
                    if ($contact->password) {
                        $contact->password = decrypt($contact->password);
                    }
                    return $contact;
                }) 
                : null,
            'status'       => $this->status,
            'created_by'  => $this->createdBy?->name,
            'updated_by'  => $this->updatedBy?->name,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            ...$customFields,
        ];
    }
}
