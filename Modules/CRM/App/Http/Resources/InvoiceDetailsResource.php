<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        return [
            'id'                            => $this->id,
            'company'                       => $this->whenLoaded('company', function () {
                return [
                    'id'   => $this->company?->id,
                    'name' => $this->company?->name,
                ];
            }),
            'contact'                       => $this->whenLoaded('contact', function () {
                return [
                    'id'   => $this->contact?->id,
                    'name' => $this->contact?->first_name . ' ' . $this->contact?->last_name,

                ];
            }),
            'can_apply_to_credit'           => $this->contact?->creditNote ? $this->contact?->creditNote?->remaining_credit > 0 : false,
            'credit_note'                   => $this->contact?->creditNote,
            'invoice_number'                => $this->invoice_number,
            'reference_number'              => $this->reference_number,
            'invoice_date'                  => $this->invoice_date->format('Y-m-d'),
            'due_date'                      => $this->due_date?->format('Y-m-d'),
            'currency'                      => $this->whenLoaded('currency', function () {
                return [
                    'id'     => $this->currency?->id,
                    'name'   => $this->currency?->name,
                    'code'   => $this->currency?->code,
                    'symbol' => $this->currency?->symbol,
                ];
            }),
            'payment_term'                  => $this->whenLoaded('paymentTerm', function () {
                return [
                    'id'   => $this->paymentTerm?->id,
                    'name' => $this->paymentTerm?->name,
                ];
            }),
            'project'                       => $this->whenLoaded('project', function () {
                return [
                    'id'   => $this->project?->id,
                    'name' => $this->project?->name,
                ];
            }),
            'sale_person'                   => $this->whenLoaded('salePerson', function () {
                return [
                    'id'   => $this->salePerson?->id,
                    'name' => $this->salePerson?->name,
                ];
            }),
            'item_template'                 => $this->whenLoaded('itemTemplate', function () {
                return [
                    'id'   => $this->itemTemplate?->id,
                    'name' => $this->itemTemplate?->name,
                ];
            }),
            'items'                         => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id'             => $item->item?->id,
                        'name'           => $item->item?->title,
                        'quantity'       => $item->quantity,
                        'rate'           => $item->rate,
                        'tax'            => $item->relationLoaded('tax') ? [
                            'id'   => $item->tax?->id,
                            'name' => $item->tax?->name,
                            'rate' => $item->tax?->rate,
                        ] : null,
                        'tax_amount'     => $item->tax_amount,
                        'description'    => $item->description,
                        'amount'         => $item->amount,
                        'discount_type'  => $item->discount_type,
                        'discount_value' => $item->discount_value,
                    ];
                });
            }),
            'customer_note'                 => $this->customer_note,
            'terms_and_conditions'          => $this->terms_and_conditions,
            'subtotal'                      => $this->subtotal,
            'tax'                           => $this->whenLoaded('tax', function () {
                return [
                    'id'   => $this->tax?->id,
                    'name' => $this->tax?->name,
                    'rate' => $this->tax?->rate,
                ];
            }),
            'tax_amount'                    => $this->tax_amount,
            'discount_type'                 => $this->discount_type,
            'discount_value'                => $this->discount_value,
            'grand_total'                   => $this->grand_total,
            'balance_due'                   => $this->balance_due,
            'last_payment_date'             => $this->last_payment_date,
            'enable_organization_signature' => $this->enable_organization_signature,
            'organization_signatures'       => $this->whenLoaded('organizationSignatures') ? $this->organizationSignatures->map(function ($signature) {
                return [
                    'label'           => $signature->label,
                    'assigned_signer' => $signature->relationLoaded('assignedSigner') ? [
                        'id'   => $signature->assignedSigner?->id,
                        'name' => $signature->assignedSigner?->name,
                    ] : null,
                    'notify_signer'   => $signature->notify_signer,
                    'signature_file'  => $signature->relationLoaded('signatureFile') ? [
                        'id'   => $signature->signatureFile?->id,
                        'name' => $signature->signatureFile?->name,
                        'path' => $signature->signatureFile?->path,
                    ] : null,
                    'can_sign'        => $signature->canSign,
                    'signed_at'       => $signature->signed_at?->format('Y-m-d H:i:s'),
                ];
            }) : [],
            'enable_customer_signature'     => $this->enable_customer_signature,
            'customer_signatures'           => $this->whenLoaded('customerSignatures') ? $this->customerSignatures->map(function ($signature) {
                return [
                    'label'          => $signature->label,
                    'signature_file' => $signature->relationLoaded('signatureFile') ? [
                        'id'   => $signature->signatureFile?->id,
                        'name' => $signature->signatureFile?->name,
                        'path' => $signature->signatureFile?->path,
                    ] : null,
                    'signed_at'      => $signature->signed_at?->format('Y-m-d H:i:s'),
                ];
            }) : [],
            'email_communications'          => $this->whenLoaded('emailCommunications', function () {
                return $this->emailCommunications->map(function ($email) {
                    return [
                        'id'            => $email->id,
                        'contact_id'    => $email->contact_id,
                        'contact_name'  => $email->contact?->first_name . ' ' . $email->contact?->last_name,
                        'contact_email' => $email->contact?->email,
                    ];
                });
            }),
            'file_attachments'              => $this->whenLoaded('files', function () {
                return $this->files->map(function ($file) {
                    return [
                        'id'        => $file->id,
                        'file_id'   => $file->file_id,
                        'file_name' => $file->file?->name,
                        'file_path' => $file->file?->path,
                    ];
                });
            }),
            'taxation_level'                => $this->taxation_level,
            'discount_level'                => $this->discount_level,
            'invoice_status'                => $this->invoice_status,
            'expected_payment_date'         => $this->expected_payment_date,
            'void_date'                     => $this->void_date,
            'write_off_date'                => $this->write_off_date,
            'payments'                      => $this->payments,
            'credit_note_applications'      => $this->whenLoaded('creditNoteApplications', function () {
                return $this->creditNoteApplications->map(function ($credit_note_application) {
                    return [
                        'id'                       => $credit_note_application->id,
                        'credit_note_applied_date' => $credit_note_application->credit_note_applied_date,
                        'credit_note_number'       => $credit_note_application->creditNote?->credit_note_number,
                        'payment_amount'           => $credit_note_application->payment_amount,
                    ];
                });
            }),
            'created_by'                    => $this->whenLoaded('createdBy', function () {
                return $this->createdBy?->name;
            }),
            'updated_by'                    => $this->whenLoaded('updatedBy', function () {
                return $this->updatedBy?->name;
            }),
            'created_at'                    => $this->created_at,
            'updated_at'                    => $this->updated_at,
        ];
    }
}
