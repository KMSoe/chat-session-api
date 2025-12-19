<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdwOpeningBalanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'employee_id'                 => 'required|exists:employees,id',
            'effective_date'              => 'required|date',
            'from_date'                   => 'required|date',
            'to_date'                     => 'required|date|after_or_equal:from_date',
            'total_wages_in_period'       => 'required|numeric|min:0',
            'total_worked_days_in_period' => 'required|integer|min:1',
            'adjustment_reason'           => 'nullable|string',
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
