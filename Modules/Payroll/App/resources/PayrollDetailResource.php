<?php
namespace Modules\Payroll\App\resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "id"                     => $this->id,
            "employee"               => $this->employee,
            "payroll_month"          => Carbon::parse($this->payroll_month)->format("Y-m"),
            'currency'               => $this->currency,
            'basic_salary'           => $this->basic_salary,
            "overtime_hours"         => $this->overtimeSummary?->total_hours ?? 0,
            "overtime_pay"           => $this->overtimeSummary?->amount ?? 0,
            "earnings"               => $this->earnings,
            "gross_salary"           => (float) $this->gross_salary,
            "unpaid_leave_deduction" => $this->unpaidLeaveSummary?->amount ?? 0,
            "absent_deduction"       => $this->absentSummary?->amount ?? 0,
            "deductions"             => $this->deductions,
            "total_deductions"       => (float) $this->total_deductions,
            "net_pay"                => (float) $this->net_pay,
            "status"                 => $this->status,
            "updated_by"             => $this->updatedBy,
            "slip"                   => $this->slip ? new PayrollSlipResource($this->slip) : null,
            "pay_date"               => $this->pay_date,
            "can_recalculate"        => $this->status == 'Calculated',
            "can_lock"               => $this->status == 'Calculated',
            "can_unlock"             => $this->status == 'Locked',
            "can_generate"           => $this->status == 'Locked',
            "can_preview"            => $this->status == 'Payslip Generated' || $this->status == 'Payslip Sent',
            "created_at"             => $this->created_at,
            "updated_at"             => $this->updated_at,
        ];
    }
}
