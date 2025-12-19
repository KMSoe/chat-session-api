<?php
namespace Modules\CRM\App\Http\Resources;

use App\Http\Services\CustomValuesService;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
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

        $totalTasks = 0;
        $completedTasks = 0;

        if ($this->relationLoaded('taskStatuses')) {
            foreach ($this->taskStatuses as $status) {
                if ($status->relationLoaded('tasks')) {
                    $taskCount = $status->tasks->count();
                    $totalTasks += $taskCount;
                    
                    if ($status->category === 'complete') {
                        $completedTasks += $taskCount;
                    }
                }
            }
        } else {
            $totalTasks = $this->tasks()->count();
            $completedTasks = $this->tasks()->whereHas('status', function ($query) {
                $query->where('category', 'complete');
            })->count();
        }

        return [
            'id'           => $this->id,
            'project_code' => $this->project_code,
            'name'         => $this->name,
            'description'  => $this->description,
            'owner'        => [
                'id'   => $this->owner?->id,
                'name' => $this->owner?->name,
            ],
            'status'       => $this->status,
            'start_date'   => $this->start_date,
            'end_date'     => $this->end_date,
            'task_count'   => $totalTasks,
            'completed_task_count' => $completedTasks,
            'completion_percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
            'is_active'    => $this->is_active,
            'visibility'   => $this->visibility,
            'can_delete'   => $this->tasks()->count() == 0 ? true : false,
            'applicable_to' => $this->formatApplicableTo(),
            'events'      => $this->whenLoaded('events'),
            'created_by'  => $this->createdBy?->name,
            'updated_by'  => $this->updatedBy?->name,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            ...$customFields,
        ];
    }

    protected function formatApplicableTo()
    {
        return $this->applicableTos
            ->groupBy('scope')
            ->map(fn($items, $scope) => [
                'scope' => $scope,
                'ids'   => $items->pluck('target_id')->unique()->values()->all()
            ])
            ->values()
            ->toArray();
    }
}
