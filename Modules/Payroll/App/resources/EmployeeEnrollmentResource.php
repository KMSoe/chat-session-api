<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeEnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                               => $this->employee_id,
            'employee_code'                    => $this->employee_code,
            'name'                             => $this->name,
            'joined_date'                      => $this->joined_date,
            'mpf_exempt'                       => $this->mpf_exempt ? true : false,
            'account_type'                     => $this->account_type,
            'scheme_type'                      => $this->scheme_type,
            'scheme_id'                        => $this->scheme_id,
            'scheme_name'                      => $this->scheme_name,
            'enrollment_date'                  => $this->enrollment_date,
            'employee_contribution_start_date' => $this->employee_contribution_start_date,
            'employer_contribution_start_date' => $this->employer_contribution_start_date,
            'retirement_date'                  => $this->retirement_date,
            'employee_contribution_rate'       => $this->employee_contribution_rate,
            'employer_contribution_rate'       => $this->employer_contribution_rate,
            'voluntary_rate_employee'          => $this->voluntary_rate_employee,
            'voluntary_rate_employer'          => $this->voluntary_rate_employer,
            'remarks'                          => $this->remarks,
            'created_at'                       => $this->created_at,
            'updated_at'                       => $this->updated_at,
        ];
    }
}
