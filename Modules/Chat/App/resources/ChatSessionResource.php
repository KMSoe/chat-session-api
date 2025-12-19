<?php
namespace Modules\Chat\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'session_uuid'  => $this->session_uuid,
            'current_state' => $this->current_state,
            'meta'          => $this->meta,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
