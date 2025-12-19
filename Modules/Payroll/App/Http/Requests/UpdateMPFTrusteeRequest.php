<?php
namespace Modules\Payroll\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMPFTrusteeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('mpf_trustee');

        return [
            'name'        => 'required|string|max:255|unique:mpf_trustee,name,' . $id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
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
