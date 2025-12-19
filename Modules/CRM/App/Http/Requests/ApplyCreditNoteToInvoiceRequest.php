<?php
namespace Modules\CRM\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyCreditNoteToInvoiceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'invoice_applications'                            => ['required', 'array', 'min:1'],
            'invoice_applications.*.invoice_id'               => ['required', 'exists:invoices,id'],
            'invoice_applications.*.credit_note_applied_date' => ['required', 'date_format:Y-m-d'],
            'invoice_applications.*.payment_amount'           => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
