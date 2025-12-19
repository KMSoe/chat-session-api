<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuotationInvitationLinkResource extends JsonResource
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
            'quotation' => $this->quotation->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotation_number' => $quotation->quotation_number,
                ];
            }),
            'invitation_link' => $this->invitation_token,
            'is_active' => $this->is_active,
            'expired_at' => $this->expired_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
