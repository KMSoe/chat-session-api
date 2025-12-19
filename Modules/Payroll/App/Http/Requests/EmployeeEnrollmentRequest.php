<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeEnrollmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mpf_exempt'                       => 'boolean',
            'account_type'                     => 'nullable|in:REE,CEE',
            'scheme_type'                      => 'required|in:MPF,ORSO',
            'scheme_id'                        => 'required|integer',
            'enrollment_date'                  => 'required|date',
            'employee_contribution_start_date' => 'nullable|required_if:mpf_exempt,false|date',
            'employer_contribution_start_date' => 'nullable|required_if:mpf_exempt,false|date',
            'retirement_date'                  => 'nullable|date',
            'employee_contribution_rate'       => 'nullable|required_if:mpf_exempt,false|numeric|min:0|max:100',
            'employer_contribution_rate'       => 'nullable|required_if:mpf_exempt,false|numeric|min:0|max:100',
            'voluntary_rate_employee'          => 'nullable|numeric|min:0|max:100',
            'voluntary_rate_employer'          => 'nullable|numeric|min:0|max:100',
            'remarks'                          => 'nullable|string',
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
