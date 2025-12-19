<?php
namespace Modules\Payroll\App\resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AdwOpeningBalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                          => $this->id,
            'employee'                    => $this->employee,
            'effective_date'              => Carbon::parse($this->effective_date)->format('d-M-Y'),
            'from_date'                   => Carbon::parse($this->from_date)->format('d-M-Y'),
            'to_date'                     => Carbon::parse($this->to_date)->format('d-M-Y'),
            'reference_period'            => $this->reference_period,
            'total_wages_in_period'       => number_format($this->total_wages_in_period, 2),
            'total_worked_days_in_period' => $this->total_worked_days_in_period,
            'average_daily_rate_adw'      => $this->average_daily_rate_adw,
            'adjustment_reason'           => $this->adjustment_reason,
            'created_at'                  => Carbon::parse($this->created_at)->format('d-M-Y H:i A'),
            'created_by'                  => $this->createdBy,
        ];
    }
}
