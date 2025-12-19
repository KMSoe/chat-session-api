<?php

namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\CustomValuesService;
use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ProjectFormRequest extends FormRequest
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
        $customValuesService = app(CustomValuesService::class);
        $customFieldRules    = $customValuesService->getValidationRules('project');

        return array_merge([
            'project_code' => isset($this->project) && !empty($this->project)
                    ? ['required', 'string', Rule::unique('projects', 'project_code')->ignore($this->project)->whereNull('deleted_at')]
                    : ['required', 'string', Rule::unique('projects', 'project_code')->whereNull('deleted_at')],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'owner_id' => 'required|exists:employees,id',
            'project_status_id' => 'nullable|exists:project_statuses,id',
            'is_active' => 'boolean',
            'visibility' => 'required|in:public,private,workspace',
            'applicable_to' => ['nullable', 'required_if:visibility,workspace', 'array'],
            'applicable_to.*.scope' => ['required', 'in:group,department,designation,employee'],
            'applicable_to.*.ids'   => ['required', 'array', 'min:1'],
            'applicable_to.*.ids.*' => ['integer', 'min:1'],
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
