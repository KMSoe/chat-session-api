<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateORSOSchemeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code'                                       => 'required|string|unique:orso_schemes,code',
            'name'                                       => 'required|string|max:255',
            'trustee_id'                                 => 'required|exists:mpf_trustee,id',
            'scheme_type'                                => 'required|in:Define Contribution,Define Benefit',
            'registration_no'                            => 'required|string|max:255',
            'employer_participation_no'                  => 'required|string|max:255',
            'employee_contribution_required'             => 'boolean',
            'employee_contribution_rate'                 => 'nullable|numeric|min:0',
            'employer_contribution_rate'                 => 'required|numeric|min:0',

            // Define Contribution fieldsnullable|
            'service_duration_from_year'                 => 'nullable|required_if:scheme_type,Define Contribution|integer|min:0',
            'service_duration_from_months'               => 'nullable|required_if:scheme_type,Define Contribution|integer|min:0',
            'service_duration_to_year'                   => 'nullable|required_if:scheme_type,Define Contribution|integer|min:0',
            'service_duration_to_months'                 => 'nullable|required_if:scheme_type,Define Contribution|integer|min:0',
            'vested_percentage_of_employer_contribution' => 'nullable|required_if:scheme_type,Define Contribution|numeric|min:0|max:100',

            // Define Benefit fields
            'benefit_formula'                            => 'nullable|required_if:scheme_type,Define Benefit|string',

            'eligibility_waiting_period_days'            => 'integer|min:0',
            'voluntary_contributions_allowed'            => 'boolean',
            'voluntary_rate_employer'                    => 'nullable|numeric|min:0|max:100',
            'voluntary_rate_employee'                    => 'nullable|numeric|min:0|max:100',

            'contribution_frequency'                     => 'required|string|max:255',
            'contribution_based_on'                      => 'required|string|max:255',

            'effective_from_date'                        => 'required|date',
            'termination_date'                           => 'nullable|date|after_or_equal:effective_from_date',

            'remarks'                                    => 'nullable|string',
            'is_active'                                  => 'boolean',

            'payroll_component_ids'                      => 'array',
            'payroll_component_ids.*'                    => 'exists:payroll_components,id',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
