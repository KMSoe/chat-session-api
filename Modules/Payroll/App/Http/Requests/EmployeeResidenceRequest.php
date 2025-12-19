<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeResidenceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $this->merge(['employee_id' => $this->route('employee_id')]);

        return [
            'employee_id'                 => 'required|exists:employees,id',
            'period'                      => 'required|digits:4',
            'nature'                      => 'nullable|string|max:255',
            'start_time'                  => 'nullable|date',
            'end_time'                    => 'nullable|date|after_or_equal:start_time',
            'rental_paid_by_employer'     => 'nullable|numeric|min:0',
            'rental_paid_by_employee'     => 'nullable|numeric|min:0',
            'rental_refunded_to_employee' => 'nullable|numeric|min:0',
            'rental_paid_to_employer'     => 'nullable|numeric|min:0',
            'address'                     => 'nullable|string',
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
