<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayrollPolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                          => $this->id,
            'code'                        => $this->code,
            'name'                        => $this->name,
            'pay_frequency'               => $this->pay_frequency,
            'pay_cycle_start_date'        => $this->pay_cycle_start_date,
            'pay_cycle_start_day'         => $this->pay_cycle_start_day,
            'prorata_calculation_formula' => $this->prorata_calculation_formula,
            'paid_leave_pay'              => $this->paid_leave_pay ? true : false,
            'holiday_pay'                 => $this->holiday_pay ? true : false,
            'weekoff_pay'                 => $this->weekoff_pay ? true : false,
            'is_active'                   => $this->is_active ? true : false,
            'created_at'                  => $this->created_at,
            'updated_at'                  => $this->updated_at,
        ];
    }
}
