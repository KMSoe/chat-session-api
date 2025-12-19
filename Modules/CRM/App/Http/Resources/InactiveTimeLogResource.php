<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InactiveTimeLogResource extends JsonResource
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
            'employee' => $this->relationLoaded('employee') ? $this->employee->only(['id', 'name']) : null,
            'date' => $this->date,
            'total_working_minutes' => $this->total_working_minutes,
            'active_minutes' => $this->active_minutes,
            'inactive_minutes' => $this->inactive_minutes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
