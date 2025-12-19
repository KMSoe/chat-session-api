<?php

namespace Modules\CRM\App\Http\Requests;

use App\Http\Services\ErrorService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('patch') || $this->isMethod('put');

        return[
            'company_id' => $isUpdate ? 'nullable|integer|exists:crm_companies,id' : 'required|integer|exists:crm_companies,id',
            'contact_id' => $isUpdate ? 'nullable|integer|exists:contacts,id' : 'required|integer|exists:contacts,id',
            'invoice_number' => $isUpdate ? ['nullable', 'string', Rule::unique('invoices', 'invoice_number')->ignore($this->invoice)->whereNull('deleted_at')]
                    : ['required', 'string', Rule::unique('invoices', 'invoice_number')->whereNull('deleted_at')],
            'reference_number' => 'nullable|string|max:255',
            'invoice_date' => $isUpdate ? 'nullable|date' : 'required|date',
            'currency_id' => $isUpdate ? 'nullable|integer|exists:currencies,id' : 'required|integer|exists:currencies,id',
            'payment_term_id' => $isUpdate ? 'nullable|integer|exists:crm_payment_terms,id' : 'required|integer|exists:crm_payment_terms,id',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'project_id' => 'nullable|integer|exists:projects,id',
            'sale_person_id' => 'nullable|integer|exists:employees,id',
            'item_template_id' => 'nullable|integer|exists:item_templates,id',
            'customer_note' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'subtotal' => 'nullable|numeric',
            'tax_id' => 'nullable|integer|exists:taxes,id',
            'tax_amount' => 'nullable|numeric',
            'discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'discount_value' => 'nullable|numeric',
            'grand_total' => $isUpdate ? 'nullable|numeric' : 'required|numeric',
            'enable_organization_signature' => 'nullable|boolean',
            'enable_customer_signature' => 'nullable|boolean',
            'taxation_level' => $isUpdate ? ['nullable', Rule::in(['item', 'invoice'])] : ['required', Rule::in(['item', 'invoice'])],
            'discount_level' => $isUpdate ? ['nullable', Rule::in(['item', 'invoice'])] : ['required', Rule::in(['item', 'invoice'])],
            'items' => $isUpdate ? 'nullable|array' : 'required|array',
            'items.*.item_id' =>  $isUpdate ? 'nullable|integer|exists:items,id' : 'required|integer|exists:items,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => $isUpdate ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'items.*.rate' => $isUpdate ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'items.*.amount' => $isUpdate ? 'nullable|numeric' : 'required|numeric',
            'items.*.tax_id' => 'nullable|integer|exists:taxes,id',
            'items.*.tax_amount' => 'nullable|numeric',
            'items.*.discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'items.*.discount_value' => 'nullable|numeric',
            'items.*.is_same_as_item' => $isUpdate ? 'nullable|boolean' : 'required|boolean',
            'organization_signatures' => $isUpdate ? 'nullable|array' : 'nullable|required_if:enable_organization_signature,true|array',
            'organization_signatures.*.label' => 'required_with:organization_signatures|string|max:255',
            'organization_signatures.*.assigned_signer_id' => 'nullable|integer|exists:employees,id',
            'organization_signatures.*.notify_signer' => 'nullable|boolean',
            'customer_signatures' => $isUpdate ? 'nullable|array' : 'nullable|required_if:enable_customer_signature,true|array',
            'customer_signatures.*.label' => 'required_with:customer_signatures|string|max:255',
            'files' => 'nullable|array',
            'files.*' => 'nullable|exists:files,id',
            'communications' => 'nullable|array',
            'communications.*' => 'nullable|exists:contacts,id',
            'invoice_status' => $isUpdate ? ['nullable', Rule::in(['draft', 'pending_approval', 'save_and_send'])] : ['required', Rule::in(['draft', 'pending_approval', 'save_and_send'])],
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