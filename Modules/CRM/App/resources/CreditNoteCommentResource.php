<?php

namespace Modules\CRM\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CreditNoteCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'creditNote' => [
                'id' => $this->creditNote?->id,
                'title' => $this->creditNote?->credit_note_number,
            ],
            'comment' => $this->comment,
            'commented_by' => [
                'id' => $this->commentBy?->id,
                'name' => $this->commentBy?->name,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
