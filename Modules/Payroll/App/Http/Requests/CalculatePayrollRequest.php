<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Payroll\App\Enums\PayFrequencyTypes;
use Modules\Payroll\App\Enums\PayrollHoursSourceTypes;

class CalculatePayrollRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'pay_type'                => [
                'required',
                'string',
                Rule::in(PayFrequencyTypes::values()),
            ],
            'month'                   => 'nullable|required_if:pay_type,' . PayFrequencyTypes::MONTHLY->value . '|date_format:Y-m',
            'payroll_policy_id'       => 'nullable|required_if:pay_type,' . PayFrequencyTypes::WEEKLY->value . '|exists:payroll_policies,id',
            'payroll_week_start_date' => 'nullable|required_if:pay_type,' . PayFrequencyTypes::WEEKLY->value . '|date_format:Y-m-d',
            'payroll_week_end_date'   => 'nullable|required_if:pay_type,' . PayFrequencyTypes::WEEKLY->value . '|date_format:Y-m-d',
            'payroll_dates'           => [
                'nullable',
                Rule::requiredIf(
                    in_array($this->input('pay_type'), [
                        PayFrequencyTypes::DAILY->value,
                        PayFrequencyTypes::HOURLY->value,
                    ])
                ),
                'array',
                'min:1',
            ],
            'payroll_dates.*'         => 'date_format:Y-m-d',
            'hours_source'            => [
                'nullable',
                'required_if:pay_type,' . PayFrequencyTypes::HOURLY->value,
                Rule::in(PayrollHoursSourceTypes::values()),
            ],
            'hours_worked_per_date'   => [
                'nullable',
                'required_if:hours_source,' . PayrollHoursSourceTypes::MANUAL_INPUT->value,
                'numeric',
                'gt:0',
                'lte:24',
            ],
            'applicable_to'           => ['nullable', 'array'],
            'applicable_to.*.scope'   => ['required', 'in:group,department,designation,employee'],
            'applicable_to.*.ids'     => ['required', 'array', 'min:1'],
            'applicable_to.*.ids.*'   => ['integer', 'min:1'],
            // "include_new_joiners"   => "required|boolean",
            // "include_resignees"     => "required|boolean",
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
