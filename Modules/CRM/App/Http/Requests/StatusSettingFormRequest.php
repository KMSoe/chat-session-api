<?php

namespace Modules\CRM\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatusSettingFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('patch') || $this->isMethod('put');

        return [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'color' => $isUpdate ? 'sometimes|required|string|max:7' : 'required|string|max:7',
            'category' => $isUpdate ? 'sometimes|required|in:to_do,in_progress,complete' : 'required|in:to_do,in_progress,complete',
            'is_default' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'type' => $isUpdate ? 'sometimes|required|in:project,task' : 'required|in:project,task',
        ];
    }
}
