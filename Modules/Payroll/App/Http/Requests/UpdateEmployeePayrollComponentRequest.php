<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeePayrollComponentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'data'                        => 'required|array',
            'data.*.employee_id'          => 'required|exists:employees,id',
            'data.*.payroll_component_id' => 'required|exists:payroll_components,id',
            'data.*.amount'               => 'required|numeric|min:0',
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
