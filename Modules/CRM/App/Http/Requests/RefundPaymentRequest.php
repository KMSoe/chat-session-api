<?php
namespace Modules\CRM\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefundPaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'refunded_date'    => ['required', 'date_format:Y-m-d'],
            'reference_number' => ['nullable', 'string', 'max:50'],
            'payment_mode'     => ['required', 'string', 'max:50'],
            'description'      => ['required', 'string'],
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
