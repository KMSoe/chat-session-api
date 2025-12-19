<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMPFSchemeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code'                            => 'required|string|unique:mpf_schemes,code',
            'name'                            => 'required|string',
            'trustee_id'                      => 'required|exists:mpf_trustee,id',
            'registration_no'                 => 'required|string',
            'employer_name'                   => 'required|string',
            'contact_person'                  => 'required|string',
            'phone_dial_code'                 => 'required|string',
            'phone_no'                        => 'required|string',
            'address'                         => 'required|string',
            'employer_participation_no'       => 'required|string',
            'employee_contribution_rate'      => 'required|numeric|min:0|max:100',
            'employer_contribution_rate'      => 'required|numeric|min:0|max:100',
            'minimum_income_level'            => 'required|numeric|min:0',
            'maximum_income_level'            => 'required|numeric|min:0',
            'eligibility_waiting_period_days' => 'required|integer|min:0',
            'voluntary_contributions_allowed' => 'boolean',
            'voluntary_rate_employer'         => 'nullable|numeric|min:0|max:100',
            'voluntary_rate_employee'         => 'nullable|numeric|min:0|max:100',
            'contribution_based_on'           => 'required|string',
            'remittance_file_format'          => 'required|in:XML,CSV,TXT,XLSX,PDF',
            'effective_from_date'             => 'required|date',
            'termination_date'                => 'nullable|date',
            'remarks'                         => 'nullable|string',
            'is_active'                       => 'boolean',
            'payroll_component_ids'           => 'nullable|array',
            'payroll_component_ids.*'         => 'exists:payroll_components,id',
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
