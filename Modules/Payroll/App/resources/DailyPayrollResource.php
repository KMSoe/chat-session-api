<?php
namespace Modules\Payroll\App\resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyPayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "id"                     => $this->id,
            "employee"               => $this->employee,
            "payroll_month"          => Carbon::parse($this->payroll_month)->format("F Y"),
            'currency'               => $this->currency,
            'basic_salary'           => number_format($this->basic_salary, 2),
            "overtime_hours"         => number_format($this->overtimeSummary?->total_hours ?? 0, 2),
            "overtime_pay"           => number_format($this->overtimeSummary?->amount ?? 0, 2),
            "earnings"               => $this->all_earnings->map(function ($component) {
                $employeeComponent = collect($this->earnings)->firstWhere('payroll_component_id', $component->id);

                return (object) [
                    'id'     => $component->id,
                    'name'   => $component->name,
                    'amount' => $employeeComponent ? number_format($employeeComponent->amount, 2) : 'N/A',
                ];
            }),
            "gross_salary"           => number_format((float) $this->gross_salary, 2),
            "unpaid_leave_deduction" => number_format($this->unpaidLeaveSummary?->amount ?? 0, 2),
            "absent_deduction"       => number_format($this->absentSummary?->amount ?? 0, 2),
            "deductions"             => $this->all_deductions->map(function ($component) {
                $employeeComponent = collect($this->deductions)->firstWhere('payroll_component_id', $component->id);

                return (object) [
                    'id'     => $component->id,
                    'name'   => $component->name,
                    'amount' => $employeeComponent ? number_format($employeeComponent->amount, 2) : 'N/A',
                ];
            }),
            "total_deductions"       => number_format((float) $this->total_deductions, 2),
            "net_pay"                => number_format((float) $this->net_pay, 2),
            "status"                 => $this->status,
            "updated_by"             => $this->updatedBy,
            "slip"                   => $this->slip ? new PayrollSlipResource($this->slip) : null,
            "pay_date"               => $this->pay_date,
            "can_recalculate"        => $this->status == 'Calculated',
            "can_lock"               => $this->status == 'Calculated',
            "can_unlock"             => $this->status == 'Locked',
            "can_generate"           => $this->status == 'Locked',
            "can_send"               => $this->status == 'Locked' ? true : false,
            "can_preview"            => $this->status == 'Locked' || $this->status == 'Payslip Sent',
            "created_at"             => $this->created_at,
            "updated_at"             => $this->updated_at,
        ];
    }
}
