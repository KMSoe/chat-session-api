<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollSlipTemplateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'                                        => 'required|string|unique:payroll_slip_templates,name,' . $this->route('payrollslip_template'),
            'description'                                 => 'nullable|string',
            'is_default'                                  => 'boolean',
            'remove_components_with_zero_amount'          => 'boolean',
            'logo_file_id'                                => 'nullable|exists:files,id',
            'company_info_items'                          => 'required|array',
            'employee_info_items'                         => 'required|array',
            'enable_leave_section'                        => 'boolean',

            'earning_components'                          => 'nullable|array',
            'earning_components.*.payroll_component_id'   => 'required|integer|exists:payroll_components,id',
            'earning_components.*.order'                  => 'nullable|integer',

            'deduction_components'                        => 'nullable|array',
            'deduction_components.*.payroll_component_id' => 'required|integer|exists:payroll_components,id',
            'deduction_components.*.order'                => 'nullable|integer',
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
