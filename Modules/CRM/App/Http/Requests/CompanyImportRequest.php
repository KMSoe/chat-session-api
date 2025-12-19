<?php

namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class CompanyImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'code' => isset($this->company) && !empty($this->company)
                    ? ['required', 'string', Rule::unique('crm_companies', 'company_code')->ignore($this->company)->whereNull('deleted_at')]
                    : ['required', 'string', Rule::unique('crm_companies', 'company_code')->whereNull('deleted_at')],
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'phone_dial_code' => 'nullable|string|max:10',
            'phone_number' => 'nullable|string|max:20',
            'tax' => 'nullable|exists:taxes,id',
            'currency' => 'nullable|exists:currencies,id',
            'billing_country' => 'nullable|exists:countries,id',
            'billing_state' => 'nullable|exists:states,id',
            'billing_district' => 'nullable|string|max:255',
            'billing_zip_code' => 'nullable|string|max:20',
            'billing_address_line_1' => 'nullable|string|max:500',
            'billing_address_line_2' => 'nullable|string|max:500',
            'billing_phone_dial_code' => 'nullable|string|max:10',
            'billing_phone_number' => 'nullable|string|max:20',
            'shipping_same_as_billing' => 'nullable|in:yes,no',   
            'shipping_country' => 'nullable|exists:countries,id',
            'shipping_state' => 'nullable|exists:states,id',
            'shipping_district' => 'nullable|string|max:255',
            'shipping_zip_code' => 'nullable|string|max:20',
            'shipping_address_line_1' => 'nullable|string|max:500',
            'shipping_address_line_2' => 'nullable|string|max:500',
            'shipping_phone_dial_code' => 'nullable|string|max:10',
            'shipping_phone_number' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'errors'      => $validator->errors()
        ], ErrorService::UNPROCESSABLE_CONTENT));
    }
}
