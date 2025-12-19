<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeTaxFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $this->merge(['employee_id' => $this->route('employee_id')]);

        return [
            'employee_id'                    => 'required|exists:employees,id',
            'passport_no'                    => 'nullable|string|max:50',
            'passport_place_of_issue'        => 'nullable|string|max:100',
            'spouse_full_name'               => 'nullable|string|max:255',
            'spouse_id_card'                 => 'nullable|string|max:50',
            'spouse_passport_no'             => 'nullable|string|max:50',
            'spouse_passport_place_of_issue' => 'nullable|string|max:100',
            'region_code'                    => 'nullable|string|max:10',
            'principal_employer_name'        => 'nullable|string|max:255',
            'tax_identity'                   => 'nullable|string|max:100',
            'other_income_name'              => 'nullable|string|max:255',
            'same_as_address'                => 'boolean',
            'postal_address'                 => 'nullable|string',
            'employer_provides_residence'    => 'boolean',
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
