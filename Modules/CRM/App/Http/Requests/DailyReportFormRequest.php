<?php
namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\CustomValuesService;
use App\Http\Services\ErrorService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\CRM\App\Models\ItemTypeAttribute;

class DailyReportFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_clock_in'   => 'sometimes|date_format:d-m-Y H:i:s',
            'daily_reports'   => 'required|array|min:1',
            'daily_reports.*.date'                  => 'required|date_format:d-m-Y',
            'daily_reports.*.total_minutes' => 'required|integer|min:0',
            'daily_reports.*.active_minutes'        => 'required|integer|min:0',
            'daily_reports.*.inactive_minutes'      => 'required|integer|min:0',
            'daily_reports.*.marked_inactive'     => 'required|boolean',
            'daily_reports.*.tasks'        => 'required|array',
            'daily_reports.*.tasks.*.task_id' => 'required|exists:tasks,id',
            'daily_reports.*.tasks.*.start_time' => 'required|date_format:H:i:s',
            'daily_reports.*.tasks.*.end_time' => 'required|date_format:H:i:s',
            'daily_reports.*.tasks.*.duration_minutes' => 'required|integer|min:0',
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
