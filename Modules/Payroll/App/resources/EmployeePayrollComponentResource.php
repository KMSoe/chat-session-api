<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePayrollComponentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "id"                 => $this->id,
            "employee_code"      => $this->employee_code,
            "name"               => $this->name,
            "departments"        => $this->departments,
            "payroll_components" => $this->payrollComponents->map(function ($epc) {
                return [
                    "id"                   => $epc->id,
                    "payroll_component_id" => $epc->payroll_component_id,
                    "component_code"       => $epc->component?->code,
                    "component_name"       => $epc->component?->name,
                    "amount"               => $epc->amount,
                ];
            }),
        ];

    }
}
