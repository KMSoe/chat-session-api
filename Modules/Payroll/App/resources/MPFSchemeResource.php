<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MPFSchemeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                              => $this->id,
            'code'                            => $this->code,
            'name'                            => $this->name,
            'registration_no'                 => $this->registration_no,
            'employer_name'                   => $this->employer_name,
            'contact_person'                  => $this->contact_person,
            'phone_dial_code '                => $this->phone_dial_code,
            'phone_no'                        => $this->phone_no,
            'address'                         => $this->address,
            'employer_participation_no'       => $this->employer_participation_no,
            'employee_contribution_rate'      => $this->employee_contribution_rate,
            'employer_contribution_rate'      => $this->employer_contribution_rate,
            'minimum_income_level'            => $this->minimum_income_level,
            'maximum_income_level'            => $this->maximum_income_level,
            'eligibility_waiting_period_days' => $this->eligibility_waiting_period_days,
            'voluntary_contributions_allowed' => (bool) $this->voluntary_contributions_allowed,
            'voluntary_rate_employer'         => $this->voluntary_rate_employer,
            'voluntary_rate_employee'         => $this->voluntary_rate_employee,
            'contribution_based_on'           => $this->contribution_based_on,
            'remittance_file_format'          => $this->remittance_file_format,
            'effective_from_date'             => $this->effective_from_date,
            'termination_date'                => $this->termination_date,
            'remarks'                         => $this->remarks,
            'is_active'                       => (bool) $this->is_active,
            'trustee'                         => $this->trustee,
            'payroll_components'              => $this->payrollComponents,
            'created_at'                      => $this->created_at,
            'updated_at'                      => $this->updated_at,
        ];
    }
}
