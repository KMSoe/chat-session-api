<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskTimeLogResource extends JsonResource
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
            'task' => $this->task->only(['id', 'title']),
            'employee' => $this->employee->only(['id', 'name']),
            'total_duration_minutes' => $this->total_duration_minutes,
            'log_date' => $this->log_date,
            'status' => $this->status,
            'details' => TaskTimeLogDetailResource::collection($this->whenLoaded('timeLogDetails')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
