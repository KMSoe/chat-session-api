<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxCalculationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "id"               => $this->id,
            "tax_form"         => $this->taxForm,
            "period"           => $this->period,
            "number_of_people" => $this->number_of_people,
            "total_income"     => $this->total_income,
            "status"           => $this->status,
            "created_at"       => $this->created_at,
            "updated_at"       => $this->updated_at,
        ];
    }
}
