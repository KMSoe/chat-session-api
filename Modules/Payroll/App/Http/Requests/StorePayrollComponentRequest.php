<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Payroll\App\Enums\PayrollComponentCalculationTypes;
use Modules\Payroll\App\Enums\PayrollComponentScopeModes;
use Modules\Payroll\App\Enums\PayrollComponentTypes;

class StorePayrollComponentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code'                              => [
                'required',
                'string',
                'max:50',
                'unique:payroll_components,code',
            ],
            'name'                              => 'required|string|max:100|unique:payroll_components,name',
            'payroll_component_category_id'     => 'nullable|exists:payroll_component_categories,id',
            'component_type'                    => [
                'required',
                'string',
                Rule::in(PayrollComponentTypes::values()),
            ],
            'component_scope_mode'              => [
                'required',
                'string',
                Rule::in(PayrollComponentScopeModes::values()),
            ],
            'calculation_type'                  => [
                'required_if:component_scope_mode,' . PayrollComponentScopeModes::SAME_AMOUNT_FOR_ALL->value,
                'string',
                Rule::in(PayrollComponentCalculationTypes::values()),
            ],
            'amount'                            => [
                'nullable',
                Rule::requiredIf(function () {
                    return $this->component_scope_mode === PayrollComponentScopeModes::SAME_AMOUNT_FOR_ALL->value
                    && $this->calculation_type === PayrollComponentCalculationTypes::FIXED_AMOUNT->value;
                }),
                'numeric',
                'min:0',
            ],
            'rate'                              => [
                'nullable',
                Rule::requiredIf(function () {
                    return $this->component_scope_mode === PayrollComponentScopeModes::SAME_AMOUNT_FOR_ALL->value
                    && $this->calculation_type === PayrollComponentCalculationTypes::PERCENTAGE->value;
                }),
                'numeric',
                'min:0',
            ],
            'system_build_in_payroll_component' => [
                'nullable',
                'string',
                Rule::requiredIf(function () {
                    return $this->component_scope_mode === PayrollComponentScopeModes::SAME_AMOUNT_FOR_ALL->value
                    && $this->calculation_type === PayrollComponentCalculationTypes::PERCENTAGE->value;
                }),
                'exists:system_build_in_components,code',
            ],
            'formula'                           => [
                'nullable',
                'string',
                Rule::requiredIf(function () {
                    return $this->component_scope_mode === PayrollComponentScopeModes::SAME_AMOUNT_FOR_ALL->value
                    && $this->calculation_type === PayrollComponentCalculationTypes::FORMULA->value;
                }),
            ],
            'taxable'                           => 'boolean',
            'affects_net_pay'                   => 'boolean',
            'recurring'                         => 'boolean',
            'effected_months'                   => 'nullable|required_if:recurring,false|array',
            'effected_months.*'                 => 'date_format:Y-m',
            'employment_types'                  => 'nullable|array',
            'employment_types.*'                => 'string',
            'remarks'                           => 'nullable|string|max:255',
            'is_active'                         => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'amount.required_if'                            => 'The amount field is required when calculation type is Fixed Amount',
            'rate.required_if'                              => 'The rate field is required when calculation type is Percentage',
            'system_build_in_payroll_component.required_if' => 'The base component field is required when calculation type is Percentage',
            'formula.required_if'                           => 'The formula field is required when calculation type is Formula',
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
