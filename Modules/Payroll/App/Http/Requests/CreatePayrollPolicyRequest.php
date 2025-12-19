<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Payroll\App\Enums\PayFrequencyTypes;
use Modules\Payroll\App\Enums\ProrataSalaryDaysInMonthTypes;

class CreatePayrollPolicyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code'                        => 'required|string|max:50|unique:payroll_policies,code',
            'name'                        => 'required|string|max:255|unique:payroll_policies,name',
            'pay_frequency'               => [
                'required',
                Rule::in(PayFrequencyTypes::values()),
            ], 
            'pay_cycle_start_date'        => ['nullable', 'required_if:pay_frequency,' . PayFrequencyTypes::MONTHLY->value, 'numeric', 'min:1', 'lte:28'],
            'pay_cycle_start_day'         => [
                'nullable',
                'required_if:pay_frequency,' . PayFrequencyTypes::WEEKLY->value,
                'nullable',
                Rule::in(['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']),
            ],
            'prorata_calculation_formula' => ['nullable', 'required_if:pay_frequency,' . PayFrequencyTypes::MONTHLY->value, Rule::in(ProrataSalaryDaysInMonthTypes::values())],
            'paid_leave_pay'              => ['nullable', 'required_if:pay_frequency,' . PayFrequencyTypes::WEEKLY->value, 'boolean'],
            'holiday_pay'                 => ['nullable', 'required_if:pay_frequency,' . PayFrequencyTypes::WEEKLY->value, 'boolean'],
            'weekoff_pay'                 => ['nullable', 'required_if:pay_frequency,' . PayFrequencyTypes::WEEKLY->value, 'boolean'],
            'is_active'                   => 'boolean',
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
