<?php
namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\CustomValuesService;
use App\Http\Services\ErrorService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\CRM\App\Models\ItemTypeAttribute;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $item_type_id  = $this->input('item_type_id');
        $attribute_ids = ItemTypeAttribute::where('item_type_id', $item_type_id)->pluck('attribute_id')->toArray();

        $customValuesService = app(CustomValuesService::class);
        $customFieldRules    = $customValuesService->getValidationRulesByAttributeIds($attribute_ids);

        return array_merge([
            'title'        => isset($this->item) && ! empty($this->item)
                ? ['required', 'string', Rule::unique('items', 'title')->ignore($this->item)->whereNull('deleted_at')]
                : ['required', 'string', Rule::unique('items', 'title')->whereNull('deleted_at')],
            'description'  => 'nullable|string',
            'amount'       => 'required|numeric|min:0',
            'item_type_id' => 'required|exists:item_types,id',
        ], $customFieldRules);
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
