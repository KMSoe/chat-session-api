<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Payroll\App\Enums\PayrollComponentCalculationTypes;
use Modules\Payroll\App\Enums\PayrollComponentScopeModes;
use Modules\Payroll\App\Enums\PayrollComponentTypes;

class UpdatePayrollComponentRequest extends FormRequest
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
                Rule::unique('payroll_components', 'code')->whereNull('deleted_at')->ignore($this->payroll_component),
            ],
            'name'                              => [
                'required',
                'string',
                'max:50',
                Rule::unique('payroll_components', 'name')->whereNull('deleted_at')->ignore($this->payroll_component),
            ],
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
            'effected_months'                   => 'nullable|array|required_if:recurring,false',
            'effected_months.*'                 => 'date_format:Y-m',
            'employment_types'                  => 'nullable|array',
            'employment_types.*'                => 'string',
            'remarks'                           => 'nullable|string|max:255',
            'is_active'                         => 'boolean',
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
