<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeTaxCalculationIncomeDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "id"                       => $this->tax_form_income_category_id,
            "tax_form_income_category" => $this->taxFormIncomeCategory,
            "tax_calculation"          => $this->taxCalculation,
            "amount"                   => $this->amount,
        ];
    }
}
