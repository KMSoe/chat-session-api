<?php

namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\CustomValuesService;
use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class CompanyFormRequest extends FormRequest
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
        $customValuesService = app(CustomValuesService::class);
        $customFieldRules    = $customValuesService->getValidationRules('company');

        return array_merge([
            'company_code' => isset($this->company) && !empty($this->company)
                    ? ['required', 'string', Rule::unique('crm_companies', 'company_code')->ignore($this->company)->whereNull('deleted_at')]
                    : ['required', 'string', Rule::unique('crm_companies', 'company_code')->whereNull('deleted_at')],
            'logo_file_id' => 'nullable|exists:files,id',
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'phone_dial_code' => 'nullable|string|max:10',
            'phone_number' => 'nullable|string|max:20',
            'tax_id' => 'nullable|exists:taxes,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'billing_country_id' => 'nullable|exists:countries,id',
            'billing_state_id' => 'nullable|exists:states,id',
            'billing_district' => 'nullable|string|max:255',
            'billing_zip_code' => 'nullable|string|max:20',
            'billing_address_line_1' => 'nullable|string|max:255',
            'billing_address_line_2' => 'nullable|string|max:255',
            'billing_phone_dial_code' => 'nullable|string|max:10',
            'billing_phone_number' => 'nullable|string|max:20',
            'shipping_same_as_billing' => 'nullable|boolean',
            'shipping_country_id' => 'nullable|exists:countries,id',
            'shipping_state_id' => 'nullable|exists:states,id',
            'shipping_district' => 'nullable|string|max:255',
            'shipping_zip_code' => 'nullable|string|max:20',
            'shipping_address_line_1' => 'nullable|string|max:255',
            'shipping_address_line_2' => 'nullable|string|max:255',
            'shipping_phone_dial_code' => 'nullable|string|max:10',
            'shipping_phone_number' => 'nullable|string|max:20',
            'status' => 'nullable|boolean',
            'contacts' => 'nullable|array',
            'contacts.*.contact_code' => 'required|string|max:255',
            'contacts.*.first_name' => 'required|string|max:255',
            'contacts.*.last_name' => 'required|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.work_phone_dial_code' => 'nullable|string|max:10',
            'contacts.*.work_phone_number' => 'nullable|string|max:20',
            'contacts.*.mobile_phone_dial_code' => 'nullable|string|max:10',
            'contacts.*.mobile_phone_number' => 'nullable|string|max:20',
            'contacts.*.is_customer' => 'nullable|boolean',
            'contacts.*.is_primary' => 'nullable|boolean',
        ], $customFieldRules);
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
