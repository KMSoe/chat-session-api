<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Services\CustomValuesService;

class EmployeeTaxFileDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $employee_tax_data = [
            'id'                             => $this->employee_id,
            'employee_code'                  => $this->employee_code,
            'name'                           => $this->name,
            'joined_date'                    => $this->joined_date,
            'employment_type'                => $this->employment_type,
            "departments"                    => $this->departments?->map(function ($dept) {
                return [
                    'id'   => $dept->id,
                    'name' => $dept->name,
                ];
            }),
            "designations"                   => $this->designations?->map(function ($desig) {
                return [
                    'id'   => $desig->id,
                    'name' => $desig->name,
                ];
            }),
            'passport_no'                    => $this->passport_no,
            'passport_place_of_issue'        => $this->passport_place_of_issue,
            'spouse_full_name'               => $this->spouse_full_name,
            'spouse_id_card'                 => $this->spouse_id_card,
            'spouse_passport_no'             => $this->spouse_passport_no,
            'spouse_passport_place_of_issue' => $this->spouse_passport_place_of_issue,
            'region_code'                    => $this->region_code,
            'principal_employer_name'        => $this->principal_employer_name,
            'tax_identity'                   => $this->tax_identity,
            'other_income_name'              => $this->other_income_name,
            'same_as_address'                => (bool) $this->same_as_address,
            'postal_address'                 => $this->postal_address,
            'employer_provides_residence'    => (bool) $this->employer_provides_residence,
        ];

        $customFields = app(CustomValuesService::class)->get($this->employee_id, 'Employee');

        // Merge custom fields into the response
        if (isset($customFields) && $customFields !== []) {
            return array_merge($employee_tax_data, $customFields);
        } else {
            return $employee_tax_data;
        }
    }
}
