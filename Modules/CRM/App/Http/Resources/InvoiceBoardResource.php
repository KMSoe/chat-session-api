<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\CRM\App\Enum\InvoiceStatus;

class InvoiceBoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        // Get all invoice statuses from enum
        $invoiceStatuses = InvoiceStatus::cases();
        
        // Group invoices by status
        $invoicesByStatus = collect($invoiceStatuses)->map(function ($status) {
            $invoices = $this->resource
                ->where('invoice_status', $status->value)
                ->sortBy('sort_order')
                ->values()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'company_name' => $invoice->company?->name,
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date?->format('d-M-Y'),
                        'grand_total' => $invoice->grand_total,
                    ];
                });

            return [
                'value' => $status->value,
                'label' => $status->label(),
                'invoice_count' => $invoices->count(),
                'invoices' => $invoices,
            ];
        });

        return [
            'invoice_statuses' => $invoicesByStatus,
        ];
    }
}
