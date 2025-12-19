<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AverageDailyWageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                                  => $this->id ?? 0,
            'exclude_rest_days_from_adw'          => $this->exclude_rest_days_from_adw ?? false,
            'exclude_holidays_from_adw'           => $this->exclude_holidays_from_adw ?? false,
            'minimum_employment_period_per_week'  => $this->minimum_employment_period_per_week ?? 0,
            'minimum_employment_period_per_month' => $this->minimum_employment_period_per_month ?? 0,
            'remarks'                             => $this->remarks ?? '',
            'is_active'                           => $this->is_active ?? false,
            'created_at'                          => $this->created_at ?? null,
            'updated_at'                          => $this->updated_at ?? null,

            // Relationships
            'payroll_components'                  => $this->payrollComponents ?? [],
            'excluded_leave_types'                => $this->excludedLeaveTypes ?? [],
            'applicable_to'                       => $this->formatApplicableTo(),
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
