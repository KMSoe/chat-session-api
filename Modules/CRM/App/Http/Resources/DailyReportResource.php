<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportResource extends JsonResource
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
            'id'            => $this->id,
            'title'         => $this->title,
            'project'       => $this->whenLoaded('project', function () {
                return $this->project ? [
                    'id'   => $this->project->id,
                    'name' => $this->project->name,
                ] : null;
            }),
            'session_start' => $this->getAttribute('session_start'),
            'time_logs'     => $this->whenLoaded('timeLogs', function () {
                return $this->timeLogs->map(function ($log) {
                    return [
                        'id'                     => $log->id,
                        'total_duration_minutes' => $log->total_duration_minutes,
                        'status'                 => $log->status,
                        'date'                   => $log->log_date,
                        'details'                => $this->when($log->relationLoaded('timeLogDetails'), function () use ($log) {
                            return $log->timeLogDetails->map(function ($detail) {
                                return [
                                    'id'                => $detail->id,
                                    'start_time'        => $detail->start_time,
                                    'end_time'          => $detail->end_time,
                                    'duration_minutes'  => $detail->duration_minutes,
                                ];
                            });
                        })
                    ];
                });
            }),
        ];
    }

    protected function formatApplicableTo()
    {
        if (!$this->relationLoaded('applicableTos')) {
            return [];
        }

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
