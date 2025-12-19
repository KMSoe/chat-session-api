<?php

namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ContactImportRequest extends FormRequest
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
            'contact_code' => isset($this->contact) && !empty($this->contact)
                    ? ['required', 'string', Rule::unique('contacts', 'contact_code')->ignore($this->contact)->whereNull('deleted_at')]
                    : ['required', 'string', Rule::unique('contacts', 'contact_code')->whereNull('deleted_at')],
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => isset($this->contact) && !empty($this->contact)
                    ? ['required', 'string', Rule::unique('contacts', 'email')->ignore($this->contact)->whereNull('deleted_at')]
                    : ['required', 'string', Rule::unique('contacts', 'email')->whereNull('deleted_at')],
            'is_customer' => 'required|in:yes,no',
            'work_phone_dial_code' => 'nullable|string|max:10',
            'work_phone_number' => 'nullable|string|max:20',
            'mobile_phone_dial_code' => 'nullable|string|max:10',
            'mobile_phone_number' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|required_if:is_customer,yes|string|min:6',
            'company_name' => 'nullable|exists:crm_companies,name',
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
