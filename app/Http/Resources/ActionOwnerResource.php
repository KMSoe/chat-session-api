<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActionOwnerResource extends JsonResource
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
            'request_id' => $this->getRequestId(),
            'action_owner_id' => $this->action_owner_id,
            'action_owner_name' => $this->actionOwner?->name,
            'level' => $this->level,
            'status' => $this->status,
            'comment' => $this->comment,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get the request ID based on the foreign key available in the model
     */
    private function getRequestId()
    {
        // Check which foreign key exists and return its value
        if (isset($this->leave_request_id)) {
            return $this->leave_request_id;
        } elseif (isset($this->late_request_id)) {
            return $this->late_request_id;
        } elseif (isset($this->over_time_id)) {
            return $this->over_time_id;
        } elseif (isset($this->claim_form_id)) {
            return $this->claim_form_id;
        } elseif (isset($this->employee_exit_form_id)) {
            return $this->employee_exit_form_id;
        }
        
        return null;
    }
}
