<?php
namespace Modules\CRM\App\Http\Resources;

use App\Http\Resources\ActionOwnerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationListingResource extends JsonResource
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
            'id'          => $this->id,
            'quotation_date' => $this->quotation_date?->format('Y-m-d'),
            'quotation_number' => $this->quotation_number,
            'quotation_status' => $this->quotation_status,
            'company_name' => $this->whenLoaded('company', function () {
                return $this->company?->name;
            }),
            'client_name' => $this->whenLoaded('contact', function () {
                return $this->contact?->first_name . ' ' . $this->contact?->last_name;
            }),
            'grand_total' => $this->grand_total,
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'accepted_at' => $this->accepted_at?->format('Y-m-d H:i:s'),
            'declined_at' => $this->declined_at?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'action_owners' => $this->whenLoaded('actionOwners', function () {
                return ActionOwnerResource::collection($this->actionOwners);
            }),
            'approval_status' => $this->relationLoaded('actionOwners') && $this->actionOwners->isNotEmpty() ? $this->approval_status : 'No Approvers',
            'can_approve/reject' => ($this->status == 'pending' || $this->status == 'in_progress') ? $this->canApprove : false,
            'created_by'  => $this->createdBy?->name,
            'updated_by'  => $this->updatedBy?->name,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'editable'    => $this->quotation_status === 'draft' ? true : false,
        ];
    }
}
