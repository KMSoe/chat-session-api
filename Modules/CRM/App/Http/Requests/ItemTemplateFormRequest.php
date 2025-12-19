<?php
namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\ErrorService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ItemTemplateFormRequest extends FormRequest
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
            'name'            => isset($this->item_template) && ! empty($this->item_template)
                ? ['required', 'string', Rule::unique('item_templates', 'name')->ignore($this->item_template)->whereNull('deleted_at')]
                : ['required', 'string', Rule::unique('item_templates', 'name')->whereNull('deleted_at')],
            'description'     => 'nullable|string',
            'item_ids'       => 'nullable|array',
            'item_ids.*'     => 'exists:items,id',
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
