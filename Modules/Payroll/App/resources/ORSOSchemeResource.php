<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ORSOSchemeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                                         => $this->id,
            'code'                                       => $this->code,
            'name'                                       => $this->name,
            'trustee'                                    => $this->trustee,
            'scheme_type'                                => $this->scheme_type,
            'registration_no'                            => $this->registration_no,
            'employer_participation_no'                  => $this->employer_participation_no,
            'employee_contribution_required'             => $this->employee_contribution_required,
            'employee_contribution_rate'                 => $this->employee_contribution_rate,
            'employer_contribution_rate'                 => $this->employer_contribution_rate,

            // Contribution-specific fields
            'service_duration_from_year'                 => $this->service_duration_from_year,
            'service_duration_from_months'               => $this->service_duration_from_months,
            'service_duration_to_year'                   => $this->service_duration_to_year,
            'service_duration_to_months'                 => $this->service_duration_to_months,
            'vested_percentage_of_employer_contribution' => $this->vested_percentage_of_employer_contribution,
            'benefit_formula'                            => $this->benefit_formula,

            'eligibility_waiting_period_days'            => $this->eligibility_waiting_period_days,
            'voluntary_contributions_allowed'            => $this->voluntary_contributions_allowed,
            'voluntary_rate_employer'                    => $this->voluntary_rate_employer,
            'voluntary_rate_employee'                    => $this->voluntary_rate_employee,

            'contribution_frequency'                     => $this->contribution_frequency,
            'contribution_based_on'                      => $this->contribution_based_on,

            'effective_from_date'                        => $this->effective_from_date,
            'termination_date'                           => $this->termination_date,

            'remarks'                                    => $this->remarks,
            'is_active'                                  => $this->is_active,

            'payroll_components'                         => $this->payrollComponents,

            'created_at'                                 => $this->created_at,
            'updated_at'                                 => $this->updated_at,
        ];
    }
}
