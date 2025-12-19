<?php

namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\CustomValuesService;
use App\Http\Services\ErrorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Modules\CRM\App\Enum\QuotationStatus;
use Modules\CRM\App\Enum\InvoiceStatus;

class BoardStatusChangeRequest extends FormRequest
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
        $module = $this->input('module');
    
        $validStatuses = match($module) {
            'quotation' => QuotationStatus::values(),
            'invoice' => InvoiceStatus::values(),
            default => []
        };

        $table = match($module) {
            'quotation' => 'quotations',
            'invoice' => 'invoices',
            default => null
        };

        return [
            'id' => ['required', 'integer', $table ? Rule::exists($table, 'id')->whereNull('deleted_at') : ''],
            'module' => 'required|string|in:quotation,invoice',
            'from_status' => ['required', 'string', Rule::in($validStatuses)],
            'to_status' => ['required', 'string', Rule::in($validStatuses)],
        ];
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
