<?php
namespace Modules\CRM\App\Http\Resources;

use App\Http\Resources\ActionOwnerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceListingResource extends JsonResource
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
            'invoice_date' => $this->invoice_date?->format('Y-m-d'),
            'invoice_number' => $this->invoice_number,
            'invoice_status' => $this->invoice_status,
            'company_name' => $this->whenLoaded('company', function () {
                return $this->company?->name;
            }),
            'client_name' => $this->whenLoaded('contact', function () {
                return $this->contact?->first_name . ' ' . $this->contact?->last_name;
            }),
            'grand_total' => $this->grand_total,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'balance_due' => $this->balance_due,
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
            'editable'    => $this->invoice_status === 'draft' ? true : false,
        ];
    }
}
