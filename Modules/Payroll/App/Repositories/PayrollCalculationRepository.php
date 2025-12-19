<?php
namespace Modules\Payroll\App\Repositories;

use Carbon\Carbon;
use Modules\Attendance\Services\PayrollAttendanceService;
use Modules\Leave\App\Repositories\LeaveRequestRepository;
use Modules\OverTime\App\Repositories\OverTimeRepository;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Modules\Payroll\App\Enums\PayrollStatusTypes;
use Modules\Payroll\App\Models\EmployeeTaxCalculation;
use Modules\Payroll\App\Models\Payroll;
use Modules\Payroll\App\Models\PayrollEntry;
use Modules\Payroll\App\Models\PayrollLeaveSummary;
use Modules\Payroll\App\Services\PayrollService;
use Modules\Storage\App\Classes\ObjectStorage;

class PayrollCalculationRepository
{
    private OverTimeRepository $overTimeRepository;
    private LeaveRequestRepository $leaveRequestRepository;
    private PayrollAttendanceService $payrollAttendanceService;
    private ObjectStorage $objectStorage;

    public function __construct(OverTimeRepository $overTimeRepository,
        LeaveRequestRepository $leaveRequestRepository,
        PayrollAttendanceService $payrollAttendanceService,
        ObjectStorage $objectStorage,
    ) {
        $this->overTimeRepository       = $overTimeRepository;
        $this->leaveRequestRepository   = $leaveRequestRepository;
        $this->payrollAttendanceService = $payrollAttendanceService;
        $this->objectStorage            = $objectStorage;
    }

    public function calculateMonthlyPayroll($employee, $input)
    {
        $start_date    = $input['start_date'];
        $end_date      = $input['end_date'];
        $payroll_month = $start_date->format('Y-m-d');

        $earnings = app(PayrollService::class)->getPayrollComponentsOfEmployee($employee, PayrollComponentTypes::EARNING->value, $start_date, $end_date);
        $gross_salary = $earnings['total_amount'];

        $deductions = app(PayrollService::class)->getPayrollComponentsOfEmployee($employee, PayrollComponentTypes::DEDUCTION->value, $start_date, $end_date, $gross_salary);

        $total_deductions = $deductions['total_amount'];
        $net_pay          = $gross_salary - $total_deductions;

        $payroll = $this->storeOrUpdate([
            'employee_id'          => $employee->id,
            'currency_id'          => $employee->salary_currency_id ?? 0,
            'payroll_month'        => $payroll_month,
            'pay_frequency'        => $input['pay_frequency'],
            'pay_cycle_start_date' => $start_date,
            'pay_cycle_end_date'   => $end_date,
            'hours_worked'         => $this->payrollAttendanceService->getActualWorkingHours($employee, $start_date, $end_date),
            'basic_salary'         => $employee->basic_salary ?? 0,
            'gross_salary'         => $gross_salary,
            'total_deductions'     => $total_deductions,
            'net_pay'              => $net_pay,
            'status'               => PayrollStatusTypes::CALCULATED,
            'updated_by'           => auth()->id(),
        ]);

        $payroll->entries()->delete();

        $leave_summary_info = $this->leaveRequestRepository->getEmployeeLeaveSummary($employee, $start_date, $end_date);

        PayrollLeaveSummary::updateOrCreate([
            'payroll_id' => $payroll->id,
        ],
            [
                'payroll_id'            => $payroll->id,
                'total_carried_forward' => $leave_summary_info['total_carried_forward'] ?? 0,
                'total_entitlement'     => $leave_summary_info['total_entitlement'] ?? 0,
                'total_taken'           => $leave_summary_info['total_taken'] ?? 0,
                'total_adjusted'        => $leave_summary_info['total_adjusted'] ?? 0,
                'total_balance'         => $leave_summary_info['total_balance'] ?? 0,
            ]);

        // OT
        // PayrollOvertimeSummary::updateOrCreate([
        //     'payroll_id' => $payroll->id,
        // ],
        //     [
        //         'payroll_id'       => $payroll->id,
        //         'total_hours'      => $ot_data['total_hours'] ?? 0,
        //         'average_rate'     => $ot_data['average_rate'] ?? 0,
        //         'amount'           => $ot_data['amount'] ?? 0,
        //         'overtime_details' => $ot_data['overtime_details'] ?? [],
        //     ]);

        // Absent
        // PayrollAbsentSummary::updateOrCreate([
        //     'payroll_id' => $payroll->id,
        // ],
        //     [
        //         'payroll_id'     => $payroll->id,
        //         'days'           => $absent_deduction_data['days'] ?? 0,
        //         'daily_rate'     => $absent_deduction_data['daily_rate'] ?? 0,
        //         'amount'         => $absent_deduction_data['amount'] ?? 0,
        //         'absent_details' => $absent_deduction_data['absent_details'] ?? [],
        //     ]);

        // Unpaid Leave
        // PayrollUnpaidLeaveSummary::updateOrCreate([
        //     'payroll_id' => $payroll->id,
        // ],
        //     [
        //         'payroll_id'    => $payroll->id,
        //         'days'          => $unpaid_deduction_data['days'] ?? 0,
        //         'daily_rate'    => $unpaid_deduction_data['daily_rate'] ?? 0,
        //         'amount'        => $unpaid_deduction_data['amount'] ?? 0,
        //         'leave_details' => $unpaid_deduction_data['leave_details'] ?? [],
        //     ]);

        $components = array_merge(
            collect($earnings['components'])->map(
                function ($component) {
                    $component['type'] = PayrollComponentTypes::EARNING;
                    return $component;
                })->toArray(),
            collect($deductions['components'])->map(
                function ($component) {
                    $component['type'] = PayrollComponentTypes::DEDUCTION;
                    return $component;
                })->toArray(),
        );

        $this->savePayrollEntries($payroll, $components);

        return $payroll;
    }

    public function calculateWeeklyPayroll($employee, $input)
    {
        $start_date = $input['start_date'];
        $end_date   = $input['end_date'];

        $earnings = app(PayrollService::class)->getPayrollComponentsOfEmployee($employee, PayrollComponentTypes::EARNING->value, $start_date, $end_date);

        // $ot_data               = $this->overTimeRepository->getOverTimeHourAndFeesOfEmployee($employee, $start_date, $end_date);
        // $unpaid_leave_requests = $this->leaveRequestRepository->getUnpaidLeaveOfEmployee($employee, $start_date, $end_date);
        // $absent_records        = $this->payrollAttendanceService->getAbsentRecords($employee, $start_date, $end_date);
        // $unpaid_deduction_data = PayrollHelper::getUnpaidDeductionDetails($employee, $unpaid_leave_requests);
        // $absent_deduction_data = PayrollHelper::getAbsentDeductionDetails($employee, $absent_records);

        $weekly_rate         = $employee->weekly_pay_rate;
        $actual_working_days = $this->payrollAttendanceService->getActualWorkingDays($employee, $start_date, $end_date);
        $total_working_days  = $this->payrollAttendanceService->getTotalWorkingDays($employee, $start_date, $end_date);

        $pay_amount = $total_working_days > 0 ? ($weekly_rate * ($actual_working_days / $total_working_days)) : 0;
        $gross      = $pay_amount + $earnings['total_amount'];

        $deductions = app(PayrollService::class)->getPayrollComponentsOfEmployee($employee, PayrollComponentTypes::DEDUCTION->value, $start_date, $end_date, $gross);
        $deduct     = $deductions['total_amount'];
        $net        = $gross - $deduct;

        $payroll = $this->storeOrUpdate([
            'employee_id'          => $employee->id,
            'currency_id'          => $employee->salary_currency_id ?? 0,
            'pay_frequency'        => $input['pay_frequency'],
            'pay_cycle_start_date' => $start_date,
            'pay_cycle_end_date'   => $end_date,
            'hours_worked'         => $this->payrollAttendanceService->getActualWorkingHours($employee, $start_date, $end_date),
            'pay_amount'           => $pay_amount,
            'gross_salary'         => $gross,
            'total_deductions'     => $deduct,
            'net_pay'              => $net,
            'status'               => PayrollStatusTypes::CALCULATED,
            'updated_by'           => auth()->id(),
        ]);

        $payroll->entries()->delete();

        $leave_summary_info = $this->leaveRequestRepository->getEmployeeLeaveSummary($employee, $start_date, $end_date);

        PayrollLeaveSummary::updateOrCreate([
            'payroll_id' => $payroll->id,
        ],
            [
                'payroll_id'            => $payroll->id,
                'total_carried_forward' => $leave_summary_info['total_carried_forward'] ?? 0,
                'total_entitlement'     => $leave_summary_info['total_entitlement'] ?? 0,
                'total_taken'           => $leave_summary_info['total_taken'] ?? 0,
                'total_adjusted'        => $leave_summary_info['total_adjusted'] ?? 0,
                'total_balance'         => $leave_summary_info['total_balance'] ?? 0,
            ]);

        // OT
        // PayrollOvertimeSummary::updateOrCreate([
        //     'payroll_id' => $payroll->id,
        // ],
        //     [
        //         'payroll_id'       => $payroll->id,
        //         'total_hours'      => $ot_data['total_hours'] ?? 0,
        //         'average_rate'     => $ot_data['average_rate'] ?? 0,
        //         'amount'           => $ot_data['amount'] ?? 0,
        //         'overtime_details' => $ot_data['overtime_details'] ?? [],
        //     ]);

        // Absent
        // PayrollAbsentSummary::updateOrCreate([
        //     'payroll_id' => $payroll->id,
        // ],
        //     [
        //         'payroll_id'     => $payroll->id,
        //         'days'           => $absent_deduction_data['days'] ?? 0,
        //         'daily_rate'     => $absent_deduction_data['daily_rate'] ?? 0,
        //         'amount'         => $absent_deduction_data['amount'] ?? 0,
        //         'absent_details' => $absent_deduction_data['absent_details'] ?? [],
        //     ]);

        // Unpaid Leave
        // PayrollUnpaidLeaveSummary::updateOrCreate([
        //     'payroll_id' => $payroll->id,
        // ],
        //     [
        //         'payroll_id'    => $payroll->id,
        //         'days'          => $unpaid_deduction_data['days'] ?? 0,
        //         'daily_rate'    => $unpaid_deduction_data['daily_rate'] ?? 0,
        //         'amount'        => $unpaid_deduction_data['amount'] ?? 0,
        //         'leave_details' => $unpaid_deduction_data['leave_details'] ?? [],
        //     ]);

        $components = array_merge(
            collect($earnings['components'])->map(
                function ($component) {
                    $component['type'] = PayrollComponentTypes::EARNING;
                    return $component;
                })->toArray(),
            collect($deductions['components'])->map(
                function ($component) {
                    $component['type'] = PayrollComponentTypes::DEDUCTION;
                    return $component;
                })->toArray(),
        );

        $this->savePayrollEntries($payroll, $components);

        return $payroll;
    }

    public function calculateDailyPayroll($employee, $input)
    {
        $start_date = $input['start_date'];
        $end_date   = $input['end_date'];

        $earnings = app(PayrollService::class)->getPayrollComponentsOfEmployeeByDates(
            $employee,
            PayrollComponentTypes::EARNING->value,
            $$input['daily_pay_dates']);

        $ot_data = $this->overTimeRepository->getOverTimeHourAndFeesOfEmployee($employee, $start_date, $end_date);

        $daily_rate          = $employee->daily_pay_rate;
        $actual_working_days = $this->payrollAttendanceService->getActualWorkingDaysWithinDates($employee, $input['daily_pay_dates']);

        $pay_amount = $daily_rate * $actual_working_days;
        $gross      = $pay_amount + $earnings['total_amount'];
        $deductions = app(PayrollService::class)->getPayrollComponentsOfEmployeeByDates(
            $employee,
            PayrollComponentTypes::DEDUCTION->value,
            $$input['daily_pay_dates'],
            $gross);
        $deduct = $deductions['total_amount'];
        $net    = $gross - $deduct;

        $payroll = $this->storeOrUpdate([
            'employee_id'          => $employee->id,
            'currency_id'          => $employee->salary_currency_id ?? 0,
            'pay_frequency'        => $input['pay_frequency'],
            'pay_cycle_start_date' => $start_date,
            'pay_cycle_end_date'   => $end_date,
            'hours_worked'         => $this->payrollAttendanceService->getActualWorkingHoursWithinDates($employee, $input['daily_pay_dates']),
            'daily_rate'           => $daily_rate,
            'daily_pay_dates'      => $input['daily_pay_dates'],
            'pay_amount'           => $pay_amount,
            'gross_salary'         => $gross,
            'total_deductions'     => $deduct,
            'net_pay'              => $net,
            'status'               => PayrollStatusTypes::CALCULATED,
            'updated_by'           => auth()->id(),
        ]);

        $payroll->entries()->delete();

        $leave_summary_info = $this->leaveRequestRepository->getEmployeeLeaveSummary($employee, $start_date, $end_date);

        PayrollLeaveSummary::updateOrCreate([
            'payroll_id' => $payroll->id,
        ],
            [
                'payroll_id'            => $payroll->id,
                'total_carried_forward' => $leave_summary_info['total_carried_forward'] ?? 0,
                'total_entitlement'     => $leave_summary_info['total_entitlement'] ?? 0,
                'total_taken'           => $leave_summary_info['total_taken'] ?? 0,
                'total_adjusted'        => $leave_summary_info['total_adjusted'] ?? 0,
                'total_balance'         => $leave_summary_info['total_balance'] ?? 0,
            ]);

        // OT
        // PayrollOvertimeSummary::updateOrCreate([
        //     'payroll_id' => $payroll->id,
        // ],
        //     [
        //         'payroll_id'       => $payroll->id,
        //         'total_hours'      => $ot_data['total_hours'] ?? 0,
        //         'average_rate'     => $ot_data['average_rate'] ?? 0,
        //         'amount'           => $ot_data['amount'] ?? 0,
        //         'overtime_details' => $ot_data['overtime_details'] ?? [],
        //     ]);

        $components = array_merge(
            collect($earnings['components'])->map(
                function ($component) {
                    $component['type'] = PayrollComponentTypes::EARNING;
                    return $component;
                })->toArray(),
            collect($deductions['components'])->map(
                function ($component) {
                    $component['type'] = PayrollComponentTypes::DEDUCTION;
                    return $component;
                })->toArray(),
        );

        $this->savePayrollEntries($payroll, $components);

        return $payroll;
    }

    public function calculateHourlylyPayroll($employee, $input)
    {
        $start_date = $input['start_date'];
        $end_date   = $input['end_date'];

        $earnings = app(PayrollService::class)->getPayrollComponentsOfEmployeeByDates(
            $employee,
            PayrollComponentTypes::EARNING->value,
            $$input['daily_pay_dates']);
        

        $ot_data = $this->overTimeRepository->getOverTimeHourAndFeesOfEmployee($employee, $start_date, $end_date);

        $hourly_rate = $employee->hourly_pay_rate;

        $pay_amount = $hourly_rate * $input['total_hours'];
        $gross      = $pay_amount + $earnings['total_amount'];
        $deductions = $earnings = app(PayrollService::class)->getPayrollComponentsOfEmployeeByDates(
            $employee,
            PayrollComponentTypes::EARNING->value,
            $$input['daily_pay_dates'],
            $gross
        );
        $deduct     = $deductions['total_amount'];
        $net        = $gross - $deduct;

        $payroll = $this->storeOrUpdate([
            'employee_id'          => $employee->id,
            'currency_id'          => $employee->salary_currency_id ?? 0,
            'pay_frequency'        => $input['pay_frequency'],
            'pay_cycle_start_date' => $start_date,
            'pay_cycle_end_date'   => $end_date,
            'hours_worked'         => $input['total_hours'],
            'hourly_rate'          => $hourly_rate,
            'hourly_pay_dates'     => $input['hourly_pay_dates'],
            'pay_amount'           => $pay_amount,
            'gross_salary'         => $gross,
            'total_deductions'     => $deduct,
            'net_pay'              => $net,
            'status'               => PayrollStatusTypes::CALCULATED,
            'updated_by'           => auth()->id(),
        ]);

        $payroll->entries()->delete();

        $leave_summary_info = $this->leaveRequestRepository->getEmployeeLeaveSummary($employee, $start_date, $end_date);

        PayrollLeaveSummary::updateOrCreate([
            'payroll_id' => $payroll->id,
        ],
            [
                'payroll_id'            => $payroll->id,
                'total_carried_forward' => $leave_summary_info['total_carried_forward'] ?? 0,
                'total_entitlement'     => $leave_summary_info['total_entitlement'] ?? 0,
                'total_taken'           => $leave_summary_info['total_taken'] ?? 0,
                'total_adjusted'        => $leave_summary_info['total_adjusted'] ?? 0,
                'total_balance'         => $leave_summary_info['total_balance'] ?? 0,
            ]);

        // OT
        // PayrollOvertimeSummary::updateOrCreate([
        //     'payroll_id' => $payroll->id,
        // ],
        //     [
        //         'payroll_id'       => $payroll->id,
        //         'total_hours'      => $ot_data['total_hours'] ?? 0,
        //         'average_rate'     => $ot_data['average_rate'] ?? 0,
        //         'amount'           => $ot_data['amount'] ?? 0,
        //         'overtime_details' => $ot_data['overtime_details'] ?? [],
        //     ]);

        $components = array_merge(
            collect($earnings['components'])->map(
                function ($component) {
                    $component['type'] = PayrollComponentTypes::EARNING;
                    return $component;
                })->toArray(),
            collect($deductions['components'])->map(
                function ($component) {
                    $component['type'] = PayrollComponentTypes::DEDUCTION;
                    return $component;
                })->toArray(),
        );

        $this->savePayrollEntries($payroll, $components);

        return $payroll;
    }

    public function updateEmployeeTaxCalculation($payroll, $pay_cycle_end_date)
    {
        $pay_cycle_end_date = Carbon::parse($pay_cycle_end_date);
        $earning_entries    = PayrollEntry::where('payroll_id', $payroll->id)->where('type', PayrollComponentTypes::EARNING->value)->get();
        $deduction_entries  = PayrollEntry::where('payroll_id', $payroll->id)->where('type', PayrollComponentTypes::DEDUCTION->value)->get();

        $employee_tax_calculations = EmployeeTaxCalculation::with([
            'taxCalculation',
            'incomeDetails.taxFormIncomeCategory' => function ($query) {
                $query->with(['incomeAdditions', 'incomeDeductions']);
            },
        ])
            ->where('employee_id', $payroll->employee_id)
            ->whereHas('taxCalculation', function ($query) use ($pay_cycle_end_date) {
                $query->where(function ($q) use ($pay_cycle_end_date) {
                    $q->where(function ($q1) use ($pay_cycle_end_date) {
                        // For single period
                        $q1->where('period_type', 'single')
                            ->whereMonth('date', $pay_cycle_end_date->format('Y-m'));
                    })->orWhere(function ($q2) use ($pay_cycle_end_date) {
                        // For range period
                        $q2->where('period_type', 'range')
                            ->whereDate('start_date', '<=', $pay_cycle_end_date)
                            ->whereDate('end_date', '>=', $pay_cycle_end_date);
                    });
                });
            })
            ->get();

        foreach ($employee_tax_calculations as $key => $employee_tax_calculation) {

            foreach ($employee_tax_calculation->incomeDetails as $key => $incomeDetail) {
                $income_additions_amount  = 0;
                $income_deductions_amount = 0;

                $incomeAdditions  = $incomeDetail->taxFormIncomeCategory->incomeAdditions;
                $incomeDeductions = $incomeDetail->taxFormIncomeCategory->incomeDeductions;

                foreach ($incomeAdditions as $key => $income_component) {
                    $payroll_earn_component = collect($earning_entries)->firstWhere('payroll_component_id', $income_component->id);
                    $income_additions_amount += $payroll_earn_component ? $payroll_earn_component->amount : 0;
                }

                foreach ($incomeDeductions as $key => $deduct_component) {
                    $payroll_deduct_component = collect($deduction_entries)->firstWhere('payroll_component_id', $deduct_component->id);

                    $income_deductions_amount += $payroll_deduct_component ? $payroll_deduct_component->amount : 0;
                }

                $incomeDetail->amount += $income_additions_amount - $income_deductions_amount;
                $incomeDetail->save();
            }

        }
    }

    public function storeOrUpdate($data)
    {
        $pay_cycle_start_date = Carbon::parse($data['pay_cycle_start_date']);

        $payroll = Payroll::where('employee_id', $data['employee_id'])
            ->where('pay_frequency', $data['pay_frequency'])
            ->where('pay_cycle_start_date', $pay_cycle_start_date->format('Y-m-d'))
            ->first();

        if (! $payroll) {
            return Payroll::create($data);
        }

        $payroll->update($data);

        return $payroll;
    }

    public function savePayrollEntries($payroll, $components)
    {
        $entries = array_map(function ($component) use ($payroll) {
            return [
                'payroll_id'           => $payroll->id,
                'payroll_component_id' => $component['id'] ?? 0,
                'type'                 => $component['type'],
                'name'                 => $component['name'],
                'affects_net_pay'      => $component['affects_net_pay'] ?? false,
                'taxable'              => $component['taxable'] ?? false,
                'amount'               => $component['amount'],
            ];
        }, $components);

        PayrollEntry::insert($entries);
    }
}
