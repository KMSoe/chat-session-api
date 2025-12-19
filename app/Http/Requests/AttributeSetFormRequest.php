<?php

namespace App\Http\Requests;

use App\Enums\AttributeType;
use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class AttributeSetFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => isset($this->attribute_set) && !empty($this->attribute_set)
                    ? ['required', 'string', Rule::unique('attribute_sets', 'name')->ignore($this->attribute_set)]
                    : ['required', 'string', Rule::unique('attribute_sets', 'name')],
            'description' => ['required', 'string'],
            'module_id' => ['required', 'exists:modules,id'],
            'attributes' => ['nullable', 'array'],
            'attributes.id' => ['exists:attributes,id'],
            'attributes.sort' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
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
