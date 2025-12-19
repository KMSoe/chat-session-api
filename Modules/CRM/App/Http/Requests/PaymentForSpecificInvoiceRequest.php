<?php
namespace Modules\CRM\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentForSpecificInvoiceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'contact_id'            => ['required', 'exists:contacts,id'],
            'payment_no'            => ['required', 'string', 'max:50'],
            'amount_received'       => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'bank_charges'          => ['nullable', 'numeric', 'min:0.00', 'max:9999999999.99'],
            'payment_date'          => ['required', 'date_format:Y-m-d'],
            'payment_mode'          => ['required', 'string', 'max:50'],
            'payment_received_date' => ['required', 'date_format:Y-m-d'],
            'notes'                 => ['nullable', 'string'],
            'communications'        => 'nullable|array',
            'communications.*'      => 'nullable|exists:contacts,id',
            'files'                 => 'nullable|array',
            'files.*'               => 'nullable|exists:files,id',
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
