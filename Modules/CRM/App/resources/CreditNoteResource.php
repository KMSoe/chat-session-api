<?php
namespace Modules\CRM\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CreditNoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'company'              => $this->whenLoaded('company', function () {
                return [
                    'id'   => $this->company?->id,
                    'name' => $this->company?->name,
                ];
            }),
            'contact'              => $this->whenLoaded('contact', function () {
                return [
                    'id'   => $this->contact?->id,
                    'name' => $this->contact?->first_name . ' ' . $this->contact?->last_name,
                ];
            }),
            'credit_note_number'   => $this->credit_note_number,
            'credit_note_date'     => $this->credit_note_date->format('Y-m-d'),
            'reference_number'     => $this->reference_number,
            'status'               => $this->status,
            'currency'             => $this->whenLoaded('currency', function () {
                return [
                    'id'     => $this->currency?->id,
                    'name'   => $this->currency?->name,
                    'code'   => $this->currency?->code,
                    'symbol' => $this->currency?->symbol,
                ];
            }),
            'item_template'        => $this->whenLoaded('itemTemplate', function () {
                return [
                    'id'   => $this->itemTemplate?->id,
                    'name' => $this->itemTemplate?->name,
                ];
            }),
            'items'                => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id'             => $item->item_id,
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
            'customer_note'        => $this->customer_note,
            'terms_and_conditions' => $this->terms_and_conditions,
            'description'          => $this->description,
            'subtotal'             => $this->subtotal,
            'tax'                  => $this->whenLoaded('tax', function () {
                return [
                    'id'   => $this->tax?->id,
                    'name' => $this->tax?->name,
                    'rate' => $this->tax?->rate,
                ];
            }),
            'taxation_level'       => $this->taxation_level,
            'discount_level'       => $this->discount_level,
            'tax_amount'           => $this->tax_amount,
            'discount_type'        => $this->discount_type,
            'discount_value'       => $this->discount_value,
            'grand_total'          => $this->grand_total,
            'credit_amount'        => $this->credit_amount,
            'credit_applied'       => $this->credit_applied,
            'remaining_credit'     => $this->remaining_credit,
            'file_attachments'     => $this->whenLoaded('files', function () {
                return $this->files->map(function ($file) {
                    return [
                        'id'        => $file->id,
                        'file_id'   => $file->file_id,
                        'file_name' => $file->file?->name,
                        'file_path' => $file->file?->path,
                    ];
                });
            }),
            'invoiceApplications'  => $this->invoiceApplications,
            'refundHistories'      => $this->refundHistories,
            'created_by'           => $this->createdBy,
            'updated_by'           => $this->updatedBy,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
