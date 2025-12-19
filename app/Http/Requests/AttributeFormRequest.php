<?php

namespace App\Http\Requests;

use App\Enums\AttributeType;
use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class AttributeFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // 'name' => isset($this->attribute) && !empty($this->attribute)
            //         ? ['required', 'string', Rule::unique('attributes', 'name')->ignore($this->attribute)->whereNull('deleted_at')]
            //         : ['required', 'string', Rule::unique('attributes', 'name')->whereNull('deleted_at')],
            'type' => ['required', new Enum(AttributeType::class)],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
