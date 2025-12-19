<?php

namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\CustomValuesService;
use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ContactFormRequest extends FormRequest
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
        $isUpdate = $this->isMethod('patch') || $this->isMethod('put');
        $customValuesService = app(CustomValuesService::class);
        $customFieldRules    = $isUpdate ? [] : $customValuesService->getValidationRules('contact');

        return array_merge([
            'contact_code' => $isUpdate
                    ? ['nullable', 'string', Rule::unique('contacts', 'contact_code')->ignore($this->contact)->whereNull('deleted_at')]
                    : ['required', 'string', Rule::unique('contacts', 'contact_code')->whereNull('deleted_at')],
            'first_name' => $isUpdate ? 'nullable|string|max:255' : 'required|string|max:255',
            'last_name' => $isUpdate ? 'nullable|string|max:255' : 'required|string|max:255',
            'company_id' => $isUpdate ? 'nullable|exists:crm_companies,id' : 'required|exists:crm_companies,id',
            'email' => $isUpdate
                    ? ['nullable', 'string', Rule::unique('contacts', 'email')->ignore($this->contact)->whereNull('deleted_at')]
                    : ['required', 'string', Rule::unique('contacts', 'email')->whereNull('deleted_at')],
            'work_phone_dial_code' => 'nullable|string|max:10',
            'work_phone_number' => 'nullable|string|max:20',
            'mobile_phone_dial_code' => 'nullable|string|max:10',
            'mobile_phone_number' => 'nullable|string|max:20',
            'is_customer' => $isUpdate ? 'nullable|boolean' : 'required|boolean',
            'is_primary' => $isUpdate ? 'nullable|boolean' : 'required|boolean',
            'status' => $isUpdate ? 'nullable|boolean' : 'required|boolean',
            'password' => $isUpdate ? 'nullable' : 'nullable|required_if:is_customer,1',
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
