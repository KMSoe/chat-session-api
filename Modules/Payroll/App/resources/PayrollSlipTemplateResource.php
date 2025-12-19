<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Storage\App\Http\Resources\FileResource;

class PayrollSlipTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $logoFile = null;

        if ($this?->logoFile ?? null) {
            $logoFile = new FileResource($this->logoFile);
        }

        return [
            'id'                                 => $this->id,
            'name'                               => $this->name,
            'description'                        => $this->description,
            'applicable_to'                      => $this->formatApplicableTo(),
            'is_default'                         => (bool) $this->is_default,
            'remove_components_with_zero_amount' => (bool) $this->remove_components_with_zero_amount,
            'logoFile'                           => $logoFile,
            'company_info_items'                 => $this->company_info_items,
            'employee_info_items'                => $this->employee_info_items,
            'enable_leave_section'               => (bool) $this->enable_leave_section,
            'earning_components'                 => $this->earningComponents,
            'deduction_components'               => $this->deductionComponents,
            'created_at'                         => $this->created_at,
            'updated_at'                         => $this->updated_at,
        ];
    }

    protected function formatApplicableTo()
    {
        return $this->applicableTos
            ->groupBy('scope')
            ->map(fn($items, $scope) => [
                'scope' => $scope,
                'ids'   => $items->pluck('target_id')->unique()->values()->all(),
            ])
            ->values()
            ->toArray();
    }
}
