<?php

namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class PaymentTermFormRequest extends FormRequest
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

        return[
            'name' => $isUpdate ? 'nullable|string|max:255' : 'required|string|max:255',
            'amount' => $isUpdate ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
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
