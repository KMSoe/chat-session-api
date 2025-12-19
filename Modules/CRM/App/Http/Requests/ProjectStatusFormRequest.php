<?php

namespace Modules\CRM\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectStatusFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('patch') || $this->isMethod('put');

        return[
            'name' => $isUpdate ? 'nullable|string|max:255' : 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'sort_order' => $isUpdate ? 'nullable|integer' : 'required|integer',
            'category' => $isUpdate ? ['nullable', Rule::in(['to_do', 'in_progress', 'complete'])] : ['required', Rule::in(['to_do', 'in_progress', 'complete'])],
        ];
    }
}
