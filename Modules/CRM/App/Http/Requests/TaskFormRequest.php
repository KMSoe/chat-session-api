<?php

namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\CustomValuesService;
use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class TaskFormRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'project_id' => 'required|exists:projects,id',
            'status_id' => 'required|exists:project_task_statuses,id',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:today',
            'sort_order' => 'nullable|integer',
            'has_reminder' => 'boolean',
            'notification_reminder' => ['nullable', 'required_if:has_reminder,true', 'in:5min,15min,1hour,1day,custom'],
            'notification_custom_minutes' => ['nullable', 'required_if:notification_reminder,custom', 'integer', 'min:0'],
            'is_active' => 'boolean',
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
