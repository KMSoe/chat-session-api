<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxFormIncomeCategoryComponentsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tax_form_income_category_id' => 'required|integer|exists:tax_form_income_categories,id',
            'income_additions'            => 'nullable|array',
            'income_additions.*'          => 'integer|exists:payroll_components,id',
            'income_deductions'           => 'nullable|array',
            'income_deductions.*'         => 'integer|exists:payroll_components,id',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tax_form_income_category_id.required' => 'Tax form income category ID is required',
            'tax_form_income_category_id.exists'   => 'The selected tax form income category does not exist',
            'income_additions.array'               => 'Income additions must be an array',
            'income_additions.*.integer'           => 'Each income addition must be an integer',
            'income_additions.*.exists'            => 'One or more income additions do not exist in payroll components',
            'income_deductions.array'              => 'Income deductions must be an array',
            'income_deductions.*.integer'          => 'Each income deduction must be an integer',
            'income_deductions.*.exists'           => 'One or more income deductions do not exist in payroll components',
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
