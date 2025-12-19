<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTermResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request3
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'amount'       => $this->amount,
            'is_active'    => $this->is_active,
            'created_by'   => $this->createdBy?->name,
            'updated_by'   => $this->updatedBy?->name,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at
        ];
    }
}
