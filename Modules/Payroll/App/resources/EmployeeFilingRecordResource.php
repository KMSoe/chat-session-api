<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeFilingRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "id"           => $this->id,
            "period"       => $this->taxCalculation?->period,
            "total_income" => $this->total_income,
            "tax_form"     => $this->taxCalculation?->taxForm?->form_type,
            "created_at"   => $this->created_at,
            "updated_at"   => $this->updated_at,
        ];
    }
}
