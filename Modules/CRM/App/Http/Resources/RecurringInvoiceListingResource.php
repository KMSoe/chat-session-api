<?php
namespace Modules\CRM\App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringInvoiceListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        $last_invoice_date = $this->logs()->latest('generated_on')->first()?->generated_on;
        $next_invoice_date = $last_invoice_date 
            ? $this->calculateNextInvoiceDate($last_invoice_date, $this->recurrence_frequency) 
            : Carbon::parse($this->start_date);

        return [
            'id'          => $this->id,
            'company_name' => $this->whenLoaded('company', function () {
                return $this->company?->name;
            }),
            'client_name' => $this->whenLoaded('contact', function () {
                return $this->contact?->first_name . ' ' . $this->contact?->last_name;
            }),
            'last_invoice_date' => $last_invoice_date?->format('Y-m-d') ?? null,
            'next_invoice_date' => $next_invoice_date?->format('Y-m-d') ?? null,
            'preference' => $this->preference,
            'grand_total' => $this->grand_total,
            'end_date' => $this->end_date?->format('Y-m-d'),
            'sales_person' => $this->whenLoaded('salePerson', function () {
                return $this->salePerson?->name;
            }),
            'created_by'  => $this->createdBy?->name,
            'updated_by'  => $this->updatedBy?->name,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }

    protected function calculateNextInvoiceDate($lastDate, $frequency)
    {
        $nextDate = Carbon::parse($lastDate)->copy();

        switch ($frequency) {
            case 'daily':
                $nextDate->addDay();
                break;
            
            case 'weekly':
                $nextDate->addWeek();
                break;
            
            case 'monthly':
                $nextDate->addMonth();
                break;
            
            case 'yearly':
                $nextDate->addYear();
                break;
            
            default:
                $nextDate->addMonth();
                break;
        }

        return $nextDate;
    }
}
