<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\CRM\App\Enum\QuotationStatus;

class QuotationBoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        // Get all quotation statuses from enum
        $quotationStatuses = QuotationStatus::cases();
        
        // Group quotations by status
        $quotationsByStatus = collect($quotationStatuses)->map(function ($status) {
            $quotations = $this->resource
                ->where('quotation_status', $status->value)
                ->sortBy('sort_order')
                ->values()
                ->map(function ($quotation) {
                    return [
                        'id' => $quotation->id,
                        'company_name' => $quotation->company?->name,
                        'quotation_number' => $quotation->quotation_number,
                        'quotation_date' => $quotation->quotation_date?->format('d-M-Y'),
                        'grand_total' => $quotation->grand_total,
                    ];
                });

            return [
                'value' => $status->value,
                'label' => $status->label(),
                'quotation_count' => $quotations->count(),
                'quotations' => $quotations,
            ];
        });

        return [
            'quotation_statuses' => $quotationsByStatus,
        ];
    }
}
