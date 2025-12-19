<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxCalculationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tax_form_id' => 'required|exists:tax_forms,id',
            'period_type' => 'required|in:range,single',
            'start_date'  => 'nullable|required_if:period_type,range|date',
            'end_date'    => 'nullable|required_if:period_type,range|date|after_or_equal:period_start_date',
            'date'        => 'nullable|required_if:period_type,single|date_format:Y-m',
        ];
    }

    public function messages()
    {
        return [
            'tax_form_id.required' => 'Tax Form is required.',
            'period_type.in'       => 'Period type must be either range or single.',
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
