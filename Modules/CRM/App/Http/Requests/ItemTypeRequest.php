<?php
namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\ErrorService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ItemTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'            => isset($this->item_type) && ! empty($this->item_type)
                ? ['required', 'string', Rule::unique('item_types', 'name')->ignore($this->item_type)->whereNull('deleted_at')]
                : ['required', 'string', Rule::unique('item_types', 'name')->whereNull('deleted_at')],
            'description'     => 'nullable|string',
            'attribute_ids'   => 'present|array',
            'attribute_ids.*' => 'exists:attributes,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors'  => $validator->errors(),
        ], ErrorService::UNPROCESSABLE_CONTENT));
    }
}
