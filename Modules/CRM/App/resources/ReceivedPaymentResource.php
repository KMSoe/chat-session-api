<?php
namespace Modules\CRM\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReceivedPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                        => $this->id,
            'contact_id'                => $this->contact_id,
            'contact'                   => $this->contact,

            'payment_no'                => $this->payment_no,
            'amount_received'           => (string) $this->amount_received,
            'bank_charges'              => (string) $this->bank_charges,
            'payment_date'              => $this->payment_date,
            'payment_mode'              => $this->payment_mode,
            'payment_received_date'     => $this->payment_received_date,
            'notes'                     => $this->notes,
            'invoice_received_payments' => $this->invoiceReceivedPayments,
            'refund_histories'          => $this->refundHistories,
            'status'                    => $this->status,
            'email_communications'      => $this->whenLoaded('emailCommunications', function () {
                return $this->emailCommunications->map(function ($email) {
                    return [
                        'id'            => $email->id,
                        'contact_id'    => $email->contact_id,
                        'contact_name'  => $email->contact?->first_name . ' ' . $email->contact?->last_name,
                        'contact_email' => $email->contact?->email,
                    ];
                });
            }),
            'file_attachments'          => $this->whenLoaded('files', function () {
                return $this->files->map(function ($file) {
                    return [
                        'id'        => $file->id,
                        'file_id'   => $file->file_id,
                        'file_name' => $file->file?->name,
                        'file_path' => $file->file?->path,
                    ];
                });
            }),
            'created_by'                => $this->createdBy,
            'updated_by'                => $this->updatedBy,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
        ];
    }
}
