<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeTaxCalculationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "id"            => $this->id,
            "employee_id"   => $this->employee_id,
            "employee_code" => $this->employee?->employee_code,
            "name"          => $this->employee?->name,
            "joined_date"   => $this->employee?->joined_date,
            "total_income"  => $this->total_income,
            "is_locked"     => $this->is_locked ? true : false,
            "created_at"    => $this->created_at,
            "updated_at"    => $this->updated_at,
        ];
    }
}
