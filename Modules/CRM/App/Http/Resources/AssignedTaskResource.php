<?php
namespace Modules\CRM\App\Http\Resources;

use App\Http\Services\CustomValuesService;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignedTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request3
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

        return [
            'id'           => $this->id,
            'task_code'    => $this->task_code,
            'title'        => $this->title,
            'project'      => $this->project?->only(['id', 'name', 'project_code']),
            'status'       => $this->status?->only(['id', 'name', 'color']),
            'description'  => $this->description,
            'due_date'     => $this->due_date ? $this->due_date->copy()->timezone(config('app.timezone'))->format('Y-m-d H:i:s') : null,
            'sort_order'   => $this->sort_order,
            'has_reminder' => $this->has_reminder,
            'notification_reminder' => $this->notification_reminder,
            'notification_custom_minutes' => $this->notification_custom_minutes,
            'is_active'    => $this->is_active,
            'applicable_to' => $this->formatApplicableTo(),
            'created_by'  => $this->createdBy?->name,
            'updated_by'  => $this->updatedBy?->name,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'timer'       => $this->timer_status ?? [
                'current_action' => 'stop',
                'start_time'     => '00:00:00',
            ],
            'total_duration' => $this->timeLogs->sum('total_duration_minutes') ?? 0,
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
