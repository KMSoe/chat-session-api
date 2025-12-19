<?php

namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectBoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
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
        }

        $completionPercentage = $totalTasks > 0 
            ? round(($completedTasks / $totalTasks) * 100, 2) 
            : 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'completion_percentage' => $completionPercentage,
            'task_statuses' => $this->whenLoaded('taskStatuses', function () {
                return $this->taskStatuses
                    ->sortBy('sort_order')
                    ->values()
                    ->map(function ($status) {
                        return [
                            'id' => $status->id,
                            'name' => $status->name,
                            'color' => $status->color,
                            'sort_order' => $status->sort_order,
                            'category' => $status->category,
                            'task_count' => $status->relationLoaded('tasks') ? $status->tasks->count() : 0,
                            'tasks' => $status->relationLoaded('tasks') 
                                ? $status->tasks
                                    ->map(function ($task) {
                                        $timeLog = $task->relationLoaded('timeLogs')
                                            ? $task->timeLogs->first()
                                            : null;

                                        return [
                                            'id' => $task->id,
                                            'title' => $task->title,
                                            'due_date'     => $task->due_date ? $task->due_date->copy()->timezone(config('app.timezone'))->format('Y-m-d H:i:s') : null,
                                            'sort_order' => $task->sort_order,
                                            'is_active' => $task->is_active,
                                            'task_status' => $task->getAttribute('latest_time_log_status') ?? 'stopped',
                                            'is_applicable_to_user' => (bool) ($task->getAttribute('is_applicable_to_user') ?? true),
                                        ];
                                    })
                                : [],
                        ];
                    });
            }, [])
        ];
    }
}