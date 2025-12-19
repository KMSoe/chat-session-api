<?php
namespace Modules\Payroll\App\resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollComponentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                                => $this->id,
            'code'                              => $this->code,
            'name'                              => $this->name,
            'payroll_component_category'        => $this->category,
            'component_type'                    => $this->component_type,
            'component_scope_mode'              => $this->component_scope_mode,
            'calculation_type'                  => $this->calculation_type,
            'amount'                            => $this->amount,
            'rate'                              => $this->rate,
            'system_build_in_payroll_component' => $this->system_build_in_payroll_component,
            'formula'                           => $this->formula,
            'taxable'                           => $this->taxable,
            'affects_net_pay'                   => $this->affects_net_pay,
            'recurring'                         => $this->recurring,
            'effected_months'                   => $this->effected_months,
            'employment_types'                  => $this->employment_types,
            'remarks'                           => $this->remarks,
            'is_active'                         => $this->is_active,
            'is_system_default'                 => $this->is_system_default ? true : false,
            'cannot_edit_delete'                => $this->is_system_default ? true : false,
            'applicable_to'                     => $this->formatApplicableTo(),
            'created_at'                        => $this->created_at?->toDateTimeString(),
            'updated_at'                        => $this->updated_at?->toDateTimeString(),

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
