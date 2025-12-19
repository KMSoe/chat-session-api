<?php
namespace Modules\CRM\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreditNoteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('patch') || $this->isMethod('put');

        return [
            'company_id'              => $isUpdate ? 'nullable|integer|exists:crm_companies,id' : 'required|integer|exists:crm_companies,id',
            'contact_id'              => $isUpdate ? 'nullable|integer|exists:contacts,id' : 'required|integer|exists:contacts,id',
            'credit_note_number'      => $isUpdate ? [Rule::unique('credit_notes', 'credit_note_number')->whereNull('deleted_at')->ignore($this->credit_note)] : 'required|string|unique:credit_notes,credit_note_number',
            'credit_note_date'        => $isUpdate ? 'nullable|date' : 'required|date',
            'reference_number'        => $isUpdate ? 'nullable|string' : 'required|string',

            'currency_id'             => 'nullable|integer|exists:currencies,id',
            'item_template_id'        => 'nullable|integer|exists:item_templates,id',
            'customer_note'           => 'nullable|string',
            'terms_and_conditions'    => 'nullable|string',
            'subtotal'                => 'nullable|numeric',
            'tax_id'                  => 'nullable|integer|exists:taxes,id',
            'tax_amount'              => 'nullable|numeric',
            'discount_type'           => ['nullable', Rule::in(['percentage', 'fixed'])],
            'discount_value'          => 'nullable|numeric',
            'grand_total'             => $isUpdate ? 'nullable|numeric' : 'required|numeric',
            'taxation_level'          => $isUpdate ? ['nullable', Rule::in(['item', 'invoice'])] : ['required', Rule::in(['item', 'invoice'])],
            'discount_level'          => $isUpdate ? ['nullable', Rule::in(['item', 'invoice'])] : ['required', Rule::in(['item', 'invoice'])],
            'items'                   => $isUpdate ? 'nullable|array' : 'required|array',
            'items.*.item_id'         => $isUpdate ? 'nullable|integer|exists:items,id' : 'required|integer|exists:items,id',
            'items.*.description'     => 'nullable|string',
            'items.*.quantity'        => $isUpdate ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'items.*.rate'            => $isUpdate ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'items.*.amount'          => $isUpdate ? 'nullable|numeric' : 'required|numeric',
            'items.*.tax_id'          => 'nullable|integer|exists:taxes,id',
            'items.*.tax_amount'      => 'nullable|numeric',
            'items.*.discount_type'   => ['nullable', Rule::in(['percentage', 'fixed'])],
            'items.*.discount_value'  => 'nullable|numeric',
            'items.*.is_same_as_item' => $isUpdate ? 'nullable|boolean' : 'required|boolean',

            'files'                   => 'nullable|array',
            'files.*'                 => 'nullable|exists:files,id',
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
