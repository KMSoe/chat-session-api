<?php
namespace Modules\Payroll\App\Services;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Attendance\Services\PayrollAttendanceService;
use Modules\Employee\App\Models\Employee;
use Modules\Leave\App\Repositories\LeaveRequestRepository;
use Modules\Notification\App\Services\Impl\NotificationServiceImpl;
use Modules\Notification\App\Services\NotificationService;
use Modules\OverTime\App\Repositories\OverTimeRepository;
use Modules\Payroll\App\Enums\PayFrequencyTypes;
use Modules\Payroll\App\Enums\PayrollComponentScopeModes;
use Modules\Payroll\App\Enums\PayrollHoursSourceTypes;
use Modules\Payroll\App\Enums\PayrollStatusTypes;
use Modules\Payroll\App\Enums\ProrataSalaryDaysInMonthTypes;
use Modules\Payroll\App\Helpers\PayrollHelper;
use Modules\Payroll\App\Models\EmployeePayrollComponent;
use Modules\Payroll\App\Models\Payroll;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\Repositories\EmployeeMonthlyPayrollInfoRepository;
use Modules\Payroll\App\Repositories\EmployeeWeeklyPayrollInfoRepository;
use Modules\Payroll\App\Repositories\PayrollCalculationRepository;
use Modules\Payroll\App\Repositories\PayrollRepository;
use Modules\Payroll\App\Repositories\PayrollSlipRepository;
use Modules\Storage\App\Classes\ObjectStorage;

class PayrollService
{
    protected $payrollRepository;
    protected PayrollCalculationRepository $payrollCalculationRepository;
    protected PayrollSlipRepository $payrollSlipRepository;
    private OverTimeRepository $overTimeRepository;
    private LeaveRequestRepository $leaveRequestRepository;
    private PayrollAttendanceService $payrollAttendanceService;
    private EmployeeEnrollmentService $employeeEnrollmentService;
    private NotificationService $notificationService;
    private ObjectStorage $objectStorage;

    public function __construct(
        PayrollRepository $payrollRepository,
        PayrollCalculationRepository $payrollCalculationRepository,
        PayrollSlipRepository $payrollSlipRepository,
        OverTimeRepository $overTimeRepository,
        LeaveRequestRepository $leaveRequestRepository,
        PayrollAttendanceService $payrollAttendanceService,
        EmployeeEnrollmentService $employeeEnrollmentService,
        NotificationServiceImpl $notificationService,
        ObjectStorage $objectStorage
    ) {
        $this->payrollRepository            = $payrollRepository;
        $this->payrollCalculationRepository = $payrollCalculationRepository;
        $this->payrollSlipRepository        = $payrollSlipRepository;
        $this->overTimeRepository           = $overTimeRepository;
        $this->leaveRequestRepository       = $leaveRequestRepository;
        $this->payrollAttendanceService     = $payrollAttendanceService;
        $this->employeeEnrollmentService    = $employeeEnrollmentService;
        $this->notificationService          = $notificationService;
        $this->objectStorage                = $objectStorage;
    }

    public function findByParams(array $request)
    {
        return $this->payrollRepository->findByParams($request);
    }

    public function findById($id)
    {
        return $this->payrollRepository->findById($id);
    }

    public function getBasicSalary($employee, $start_date = null, $end_date = null)
    {
        $result = 0;

        if ($employee->is_prorata_salary == false) {
            $result = $employee->basic_salary ?? 0;
        } else {
            $start_date              = Carbon::parse($start_date);
            $end_date                = Carbon::parse($end_date);
            $prorata_calculates_days = 0;

            if ($employee->payrollPolicy?->prorata_calculation_formula == ProrataSalaryDaysInMonthTypes::TOTAL_CALENDAR_DAYS_IN_MONTH->value) {
                $prorata_calculates_days = $start_date->diffInDays($end_date) + 1;
            } else {
                $prorata_calculates_days = $this->payrollAttendanceService->getTotalWorkingDays($employee, $start_date, $end_date);
            }

            $result = $prorata_calculates_days > 0 ?
            ($employee->basic_salary / $prorata_calculates_days) * $this->payrollAttendanceService->getActualWorkingDays($employee, $start_date, $end_date)
                : 0;
        }

        return $result;
    }

    public function getWeeklylyRate($employee)
    {
        return $employee->weekly_pay_rate ?? 0;
    }

    public function getPayrollComponentValue($employee, $component_code, $start_date = null, $end_date = null, $gross_salary = 0)
    {
        $result = 0;
        switch ($component_code) {
            // ---------------- ATTENDANCE ----------------
            case 'ACTUAL_WORKING_DAYS':
                $result = $this->payrollAttendanceService->getActualWorkingDays($employee, $start_date, $end_date) ?? 0;
                break;

            case 'TOTAL_WORKING_DAYS':
                $result = $this->payrollAttendanceService->getTotalWorkingDays($employee, $start_date, $end_date) ?? 0;
                break;

            case 'ACTUAL_WORKING_HOURS':
                $result = $this->payrollAttendanceService->getActualWorkingHours($employee, $start_date, $end_date) ?? 0;
                break;

            case 'TOTAL_WORKING_HOURS':
                $result = $this->payrollAttendanceService->getTotalWorkingHours($employee, $start_date, $end_date) ?? 0;
                break;

            case 'ABSENT_COUNT':
                $result = $this->payrollAttendanceService->getAbsentCount($employee, $start_date, $end_date) ?? 0;
                break;

            case 'UNPAID_LEAVE_COUNT':
                $unpaid_leaves = $this->leaveRequestRepository->getUnpaidLeaveOfEmployee($employee, $start_date, $end_date);
                $result        = $unpaid_leaves->sum('total_days') ?? 0;
                break;

            case 'PAID_LEAVE_COUNT':
                $paid_leaves = $this->leaveRequestRepository->getPaidLeaveOfEmployee($employee, $start_date, $end_date) ?? 0;
                $result      = $paid_leaves->sum('total_days') ?? 0;
                break;

            case 'LATE_COUNT':
                $result = $this->payrollAttendanceService->getLateCount($employee, $start_date, $end_date) ?? 0;
                break;

            case 'LATE_MINUTES':
                $result = $this->payrollAttendanceService->getTotalLateMinutes($employee, $start_date, $end_date) ?? 0;
                break;

            case 'EARLY_LEAVE_COUNT':
                $result = $this->payrollAttendanceService->getEarlyLeaveCount($employee, $start_date, $end_date) ?? 0;
                break;

            case 'EARLY_LEAVE_MINUTES':
                $result = $this->payrollAttendanceService->getTotalEarlyLeaveMinutes($employee, $start_date, $end_date) ?? 0;
                break;

            case 'OT_HOURS':
                $ot_data = $this->overTimeRepository->getOverTimeHourAndFeesOfEmployee($employee, $start_date, $end_date);
                $result  = $ot_data['hours'] ?? 0;
                break;

            // ---------------- PAYROLL ----------------
            case 'BASIC_SALARY':
                $result = $employee->basic_salary;
                break;

            case 'DAILY_RATE':
                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $result = app(EmployeeMonthlyPayrollInfoRepository::class)->getDailyRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $result = app(EmployeeWeeklyPayrollInfoRepository::class)->getDailyRate($employee);
                }

                break;

            case 'WEEKLY_RATE':
                $result = $employee->weekly_pay_rate;
                break;

            case 'HOURLY_RATE':
                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $result = app(EmployeeMonthlyPayrollInfoRepository::class)->getPerHourRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $result = app(EmployeeWeeklyPayrollInfoRepository::class)->getPerHourRate($employee, $start_date, $end_date);
                }
                break;

            case 'MINUTE_RATE':
                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $result = app(EmployeeMonthlyPayrollInfoRepository::class)->getPerMinuteRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $result = app(EmployeeWeeklyPayrollInfoRepository::class)->getPerHourRate($employee, $start_date, $end_date);
                }
                break;

            case 'OVERTIME_PAY':
                $ot_data = $this->overTimeRepository->getOverTimeHourAndFeesOfEmployee($employee, $start_date, $end_date);
                $result  = $ot_data['amount'] ?? 0;
                break;

            case 'Leave_PAY':
                $daily_rate = 0;

                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $daily_rate = app(EmployeeMonthlyPayrollInfoRepository::class)->getDailyRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $daily_rate = app(EmployeeWeeklyPayrollInfoRepository::class)->getDailyRate($employee);
                }

                $paid_leaves     = $this->leaveRequestRepository->getPaidLeaveOfEmployee($employee, $start_date, $end_date) ?? 0;
                $paid_leave_data = PayrollHelper::getLeavePayDetails($employee, $daily_rate, $paid_leaves);
                $result          = $paid_leave_data['amount'] ?? 0;
                break;

            case 'HOLIDAY_PAY':
                $daily_rate = 0;

                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $daily_rate = app(EmployeeMonthlyPayrollInfoRepository::class)->getDailyRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $daily_rate = app(EmployeeWeeklyPayrollInfoRepository::class)->getDailyRate($employee);
                }

                $holiday_count = $this->payrollAttendanceService->getHolidaysCount($employee, $start_date, $end_date);
                $result        = $holiday_count * $daily_rate;
                break;

            case 'WEEK_OFF_PAY':
                $daily_rate = 0;

                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $daily_rate = app(EmployeeMonthlyPayrollInfoRepository::class)->getDailyRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $daily_rate = app(EmployeeWeeklyPayrollInfoRepository::class)->getDailyRate($employee);
                }

                $week_off_count = $this->payrollAttendanceService->getOffDaysCount($employee, $start_date, $end_date);
                $result         = $week_off_count * $daily_rate;
                break;

            case 'LATE_MINUTES_DEDUCTION':
                $late_minutes    = $this->payrollAttendanceService->getTotalLateMinutes($employee, $start_date, $end_date) ?? 0;
                $per_minute_rate = 0;
                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $per_minute_rate = app(EmployeeMonthlyPayrollInfoRepository::class)->getPerMinuteRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $per_minute_rate = app(EmployeeWeeklyPayrollInfoRepository::class)->getPerHourRate($employee, $start_date, $end_date);
                }

                $result = $late_minutes * $per_minute_rate;
                break;

            case 'EARLY_OUT_DEDUCTION':
                $early_out_minutes = $this->payrollAttendanceService->getTotalEarlyLeaveMinutes($employee, $start_date, $end_date) ?? 0;

                $per_minute_rate = 0;
                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $per_minute_rate = app(EmployeeMonthlyPayrollInfoRepository::class)->getPerMinuteRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $per_minute_rate = app(EmployeeWeeklyPayrollInfoRepository::class)->getPerHourRate($employee, $start_date, $end_date);
                }

                $result = $early_out_minutes * $per_minute_rate;

                break;

            case 'UNPAID_LEAVE_DEDUCTION':
                $daily_rate = 0;

                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $daily_rate = app(EmployeeMonthlyPayrollInfoRepository::class)->getDailyRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $daily_rate = app(EmployeeWeeklyPayrollInfoRepository::class)->getDailyRate($employee);
                }

                $unpaid_leaves = $this->leaveRequestRepository->getUnpaidLeaveOfEmployee($employee, $start_date, $end_date);
                $unpaid_data   = PayrollHelper::getUnpaidDeductionDetails($employee, $daily_rate, $unpaid_leaves);
                $result        = $unpaid_data['amount'] ?? 0;
                break;

            case 'ABSENT_DEDUCTION':
                $daily_rate = 0;

                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                    $daily_rate = app(EmployeeMonthlyPayrollInfoRepository::class)->getDailyRate($employee, $start_date, $end_date);
                } else if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                    $daily_rate = app(EmployeeWeeklyPayrollInfoRepository::class)->getDailyRate($employee);
                }

                $absent_records = $this->payrollAttendanceService->getAbsentRecords($employee, $start_date, $end_date);
                $absent_data    = PayrollHelper::getAbsentDeductionDetails($employee, $daily_rate, $absent_records);
                $result         = $absent_data['amount'] ?? 0;
                break;

            case 'MPF_EMPLOYEE':
                $result = $this->employeeEnrollmentService->getMPFEmployee($employee, $gross_salary);
                break;

            case 'MPF_EMPLOYER':
                $result = $this->employeeEnrollmentService->getMPFEmployer($employee, $gross_salary);
                break;

            case 'ORSO_EMPLOYEE':
                $result = $this->employeeEnrollmentService->getORSOEmployee($employee, $gross_salary);
                break;

            case 'ORSO_EMPLOYER':
                $result = $this->employeeEnrollmentService->getORSOEmployer($employee, $gross_salary);
                break;

            default:
                $result = 0;
                break;
        }

        return $result;
    }

    public function getPayrollComponentValueByDatesArray($employee, $component_code, $dates, $gross_salary = 0)
    {
        $result = 0;
        switch ($component_code) {
            // ---------------- ATTENDANCE ----------------
            case 'ACTUAL_WORKING_DAYS':
                $result = $this->payrollAttendanceService->getActualWorkingDaysWithinDates($employee, $dates) ?? 0;
                break;

            case 'TOTAL_WORKING_DAYS':
                $result = $this->payrollAttendanceService->getTotalWorkingDaysWithinDates($employee, $dates) ?? 0;
                break;

            case 'ACTUAL_WORKING_HOURS':
                $result = $this->payrollAttendanceService->getActualWorkingDaysWithinDates($employee, $dates) ?? 0;
                break;

            case 'TOTAL_WORKING_HOURS':
                $result = $this->payrollAttendanceService->getTotalWorkingHoursWithinDates($employee, $dates) ?? 0;
                break;

            case 'ABSENT_COUNT':
                $result = $this->payrollAttendanceService->getAbsentCountWithinDates($employee, $dates) ?? 0;
                break;

            case 'UNPAID_LEAVE_COUNT':
                $unpaid_leaves = $this->leaveRequestRepository->getUnpaidLeaveOfEmployee($employee, $start_date, $end_date);
                $result        = $unpaid_leaves->sum('total_days') ?? 0;
                break;

            case 'PAID_LEAVE_COUNT':
                $paid_leaves = $this->leaveRequestRepository->getPaidLeaveOfEmployee($employee, $start_date, $end_date) ?? 0;
                $result      = $paid_leaves->sum('total_days') ?? 0;
                break;

            case 'LATE_COUNT':
                $result = $this->payrollAttendanceService->getLateCountWithinDates($employee, $dates) ?? 0;
                break;

            case 'LATE_MINUTES':
                $result = $this->payrollAttendanceService->getTotalLateMinutesWithinDates($employee, $dates) ?? 0;
                break;

            case 'EARLY_LEAVE_COUNT':
                $result = $this->payrollAttendanceService->getEarlyLeaveCountWithinDates($employee, $dates) ?? 0;
                break;

            case 'EARLY_LEAVE_MINUTES':
                $result = $this->payrollAttendanceService->getTotalEarlyLeaveMinutesWithinDates($employee, $dates) ?? 0;
                break;

            case 'OT_HOURS':
                $ot_data = $this->overTimeRepository->getOverTimeHourAndFeesOfEmployeeWithinDate($employee, $dates);
                $result  = $ot_data['hours'] ?? 0;
                break;

            // ---------------- PAYROLL ----------------
            case 'BASIC_SALARY':
                $result = $employee->basic_salary;
                break;

            // case 'DAILY_RATE':
            //     $result = $this->getDailyRate($employee);
            //     break;

            case 'WEEKLY_RATE':
                $result = $this->getWeeklylyRate($employee);
                break;

            // case 'HOURLY_RATE':
            //     $result = $this->getHourlyRate($employee);
            //     break;

            case 'OVERTIME_PAY':
                $ot_data = $this->overTimeRepository->getOverTimeHourAndFeesOfEmployeeWithinDate($employee, $dates);
                $result  = $ot_data['amount'] ?? 0;
                break;

            case 'Leave_PAY':
                // $daily_rate      = $this->getDailyRate($employee, Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1);
                // $paid_leaves     = $this->leaveRequestRepository->getPaidLeaveOfEmployee($employee, $start_date, $end_date) ?? 0;
                // $paid_leave_data = PayrollHelper::getLeavePayDetails($employee, $daily_rate, $paid_leaves);
                // $result          = $paid_leave_data['amount'] ?? 0;
                break;

            case 'LATE_MINUTES_DEDUCTION':
                $late_minutes    = $this->payrollAttendanceService->getTotalLateMinutesWithinDates($employee, $dates) ?? 0;
                $per_minute_rate = 0;
                if ($employee->payrollPolicy?->pay_frequency == PayFrequencyTypes::DAILY->value) {
                    $per_minute_rate = $this->getMinuteRateOfDailyPayEmployee($employee, $dates);
                }

                $result = $late_minutes * $per_minute_rate;
                break;

            case 'EARLY_OUT_DEDUCTION':
                $early_out_minutes = $this->payrollAttendanceService->getTotalEarlyLeaveMinutesWithinDates($employee, $dates) ?? 0;
                break;

            case 'UNPAID_LEAVE_DEDUCTION':
                // $daily_rate    = $this->getDailyRate($employee, Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1);
                // $unpaid_leaves = $this->leaveRequestRepository->getUnpaidLeaveOfEmployee($employee, $start_date, $end_date);
                // $unpaid_data   = PayrollHelper::getUnpaidDeductionDetails($employee, $daily_rate, $unpaid_leaves);
                // $result        = $unpaid_data['amount'] ?? 0;
                break;

            case 'ABSENT_DEDUCTION':
                $daily_rate     = $employee->daily_pay_rate;
                $absent_records = $this->payrollAttendanceService->getAbsentRecordsWithinDates($employee, $dates);
                $absent_data    = PayrollHelper::getAbsentDeductionDetails($employee, $daily_rate, $absent_records);
                $result         = $absent_data['amount'] ?? 0;
                break;

            case 'MPF_EMPLOYEE':
                $result = $this->employeeEnrollmentService->getMPFEmployee($employee, $gross_salary);
                break;

            case 'MPF_EMPLOYER':
                $result = $this->employeeEnrollmentService->getMPFEmployer($employee, $gross_salary);
                break;

            case 'ORSO_EMPLOYEE':
                $result = $this->employeeEnrollmentService->getORSOEmployee($employee, $gross_salary);
                break;

            case 'ORSO_EMPLOYER':
                $result = $this->employeeEnrollmentService->getORSOEmployer($employee, $gross_salary);
                break;

            default:
                $result = 0;
                break;
        }

        return $result;
    }

    public function getOtherPayrollComponentValue($payroll, $component_code)
    {
        $result = 0;
        switch ($component_code) {
            case 'BASIC_SALARY':
                $result = $payroll->basic_salary ?? 0;
                break;
            case 'PAY_AMOUNT':
                $result = $payroll->pay_amount ?? 0;
                break;
            case 'GROSS_SALARY':
                $result = $payroll->gross_salary ?? 0;
                break;
            case 'NET_SALARY':
                $result = $payroll->net_salary ?? 0;
                break;
            case 'TOTAL_DEDUCTION':
                $result = $payroll->total_deduction ?? 0;
                break;
            case 'MPF_EMPLOYER':
                $result = $this->employeeEnrollmentService->getMPFEmployee($payroll->employee, $payroll->gross_salary);
                break;
            case 'ORSO_EMPLOYER':
                $result = $this->employeeEnrollmentService->getORSOEmployer($payroll->employee, $payroll->gross_salary);
                break;
            default:
                $result = 0;
                break;
        }

        return $result;

    }
    protected static function evaluateMath($formula)
    {
        // Very basic safe eval — only numbers, operators, parentheses, decimals allowed
        if (! preg_match('/^[0-9\.\+\-\*\/\(\) ]+$/', $formula)) {
            throw new \Exception("Unsafe formula: $formula");
        }

        // Evaluate
        return eval("return {$formula};");
    }

    public function calculateFormula($employee, $formula, $start_date, $end_date)
    {
        preg_match_all('/[A-Z_]+/', $formula, $matches);

        $codes = array_unique($matches[0]);

        foreach ($codes as $code) {
            $value = $this->getPayrollComponentValue($employee, $code, $start_date, $end_date);

            // Replace code with numeric value in formula string
            $formula = str_replace($code, $value, $formula);
        }

        return $this->evaluateMath($formula);
    }

    public function calculateFormulaWithinDates($employee, $formula, $dates)
    {
        preg_match_all('/[A-Z_]+/', $formula, $matches);

        $codes = array_unique($matches[0]);

        foreach ($codes as $code) {
            $value = $this->getPayrollComponentValueByDatesArray($employee, $code, $dates);

            // Replace code with numeric value in formula string
            $formula = str_replace($code, $value, $formula);
        }

        return $this->evaluateMath($formula);
    }

    public function getPayrollComponentsOfEmployee($employee, $component_type = 'Earning', $start_date, $end_date, $months = [], $gross_salary = 0)
    {
        $start_date = Carbon::parse($start_date);
        $end_date   = Carbon::parse($end_date);

        $payroll_components = PayrollComponent::query()
            ->where('is_active', true)
            ->where('component_type', $component_type)
            ->where(function ($q) use ($employee) {
                $q->whereNull('employment_types')
                    ->orWhereJsonContains('employment_types', $employee->employment_type?->value);
            })
            ->when(true, function ($q) use ($start_date, $end_date, $months) {
                $q->where(function ($sub) use ($start_date, $end_date, $months) {
                    $sub->where('recurring', true)
                        ->orWhere(function ($nested) use ($start_date, $end_date, $months) {
                            $nested->where('recurring', false)
                                ->whereJsonContains('effected_months', $start_date->format('Y-m'))
                                ->orWhereJsonContains('effected_months', $end_date->format('Y-m'));

                            if (count($months)) {
                                foreach ($months as $month) {
                                    $nested->orWhereJsonContains('effected_months', $month);
                                }
                            }
                        });
                });
            })
            ->where(function ($query) use ($employee) {
                $query->where('is_system_default', true)
                    ->orWhere(function ($nested) use ($employee) {
                        $nested->forEmployee($employee);
                    });
            })
            ->get();

        $result = $payroll_components->map(function ($component) use ($employee, $start_date, $end_date, $gross_salary) {
            $calculatedValue = 0;

            if ($component->component_scope_mode === PayrollComponentScopeModes::SAME_AMOUNT_FOR_ALL->value) {
                if ($component->calculation_type == 'Fixed Amount') {
                    $calculatedValue = $component->amount;
                } else if ($component->calculation_type == 'Percentage') {
                    $component_value = $this->getPayrollComponentValue(
                        $employee,
                        $component->system_build_in_payroll_component,
                        $start_date,
                        $end_date,
                        $gross_salary
                    );
                    $calculatedValue = ($component->rate / 100) * $component_value;
                } else if ($component->calculation_type == 'Formula') {
                    $calculatedValue = $this->calculateFormula($employee, $component->formula, $start_date, $end_date);
                }
            }

            if ($component->component_scope_mode === PayrollComponentScopeModes::CSUTOM_AMOUNT_PER_EMPLOYEE->value) {
                $calculatedValue = EmployeePayrollComponent::query()
                    ->where('employee_id', $employee->id)
                    ->where('payroll_component_id', $component->id)->value('amount') ?? 0;
            }

            return [
                'id'               => $component->id,
                'name'             => $component->name,
                'scope_mode'       => $component->component_scope_mode,
                'affects_net_pay'  => $component->affects_net_pay ? true : false,
                'taxable'          => $component->taxable ? true : false,
                'amount'           => $calculatedValue,
                'calculated_value' => $component->affects_net_pay == true ? $calculatedValue : 0,
            ];
        });

        return [
            'components'   => $result,
            'total_amount' => $result->sum('calculated_value'),
        ];
    }

    public function getPayrollComponentsOfEmployeeByDates($employee, $component_type = 'Earning', $dates = [], $gross_salary = 0)
    {

        $months = collect($dates)->map(function ($date) {
            return Carbon::parse($date)->format('Y-m');
        });

        $payroll_components = PayrollComponent::query()
            ->where('is_active', true)
            ->where('component_type', $component_type)
            ->where(function ($q) use ($employee) {
                $q->whereNull('employment_types')
                    ->orWhereJsonContains('employment_types', $employee->employment_type?->value);
            })
            ->when(true, function ($q) use ($months) {
                $q->where(function ($sub) use ($months) {
                    $sub->where('recurring', true)
                        ->orWhere(function ($nested) use ($months) {
                            if (count($months)) {
                                foreach ($months as $month) {
                                    $nested->whereJsonContains('effected_months', $month);
                                }
                            }
                        });
                });
            })
            ->where(function ($query) use ($employee) {
                $query->where('is_system_default', true)
                    ->orWhere(function ($nested) use ($employee) {
                        $nested->forEmployee($employee);
                    });
            })
            ->get();

        $result = $payroll_components->map(function ($component) use ($employee, $dates, $gross_salary) {
            $calculatedValue = 0;

            if ($component->component_scope_mode === PayrollComponentScopeModes::SAME_AMOUNT_FOR_ALL->value) {
                if ($component->calculation_type == 'Fixed Amount') {
                    $calculatedValue = $component->amount;
                } else if ($component->calculation_type == 'Percentage') {
                    $component_value = $this->getPayrollComponentValueByDatesArray(
                        $employee,
                        $component->system_build_in_payroll_component,
                        $dates,
                        $gross_salary
                    );
                    $calculatedValue = ($component->rate / 100) * $component_value;
                } else if ($component->calculation_type == 'Formula') {
                    $calculatedValue = $this->calculateFormulaWithinDates($employee, $component->formula, $dates);
                }
            }

            if ($component->component_scope_mode === PayrollComponentScopeModes::CSUTOM_AMOUNT_PER_EMPLOYEE->value) {
                $calculatedValue = EmployeePayrollComponent::query()
                    ->where('employee_id', $employee->id)
                    ->where('payroll_component_id', $component->id)->value('amount') ?? 0;
            }

            return [
                'id'               => $component->id,
                'name'             => $component->name,
                'scope_mode'       => $component->component_scope_mode,
                'affects_net_pay'  => $component->affects_net_pay ? true : false,
                'taxable'          => $component->taxable ? true : false,
                'amount'           => $calculatedValue,
                'calculated_value' => $component->affects_net_pay == true ? $calculatedValue : 0,
            ];
        });

        return [
            'components'   => $result,
            'total_amount' => $result->sum('calculated_value'),
        ];
    }

    public function calculatePayrolls(array $data)
    {
        $pay_frequency       = $data['pay_type'];
        $include_new_joiners = $data['include_new_joiners'] ?? false;
        $include_resignees   = $data['include_resignees'] ?? false;

        $employee_ids = PayrollHelper::getEmployeeIdsFromApplicableTo($data['applicable_to']);
        $employees    = Employee::with(['payrollPolicy'])
            ->whereIn('id', $employee_ids)
            ->get();

        DB::beginTransaction();

        foreach ($employees as $key => $employee) {
            if ($employee->payrollPolicy == null) {
                abort(400, 'Setup Payroll Policy for ' . $employee->name);
            }

            if ($employee->payrollPolicy->pay_frequency != $pay_frequency) {
                abort(400, "$employee->name's Pay Frequency is $employee->payrollPolicy->pay_frequency. Not Match!");
            }

            $start_date = null;
            $end_date   = null;

            if ($pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                $month          = Carbon::createFromFormat('Y-m-d', $data['month'] . '-' . $employee->payrollPolicy?->pay_cycle_start_date);
                $pay_cycle_data = PayrollHelper::getPayCycleStartAndEnd($employee->payrollPolicy?->pay_cycle_start_date, $month);
                $start_date     = $pay_cycle_data['start_date'];
                $end_date       = $pay_cycle_data['end_date'];

                // if ($include_new_joiners == false && $start_date->format('Y-m-d') < Carbon::parse($employee->joined_date)->format('Y-m-d')) {
                //     continue;
                // }

                // if ($include_resignees == false && $end_date->format('Y-m-d') > Carbon::parse($employee->last_date)->format('Y-m-d')) {
                //     continue;
                // }

                $existingPayroll = Payroll::where('employee_id', $employee->id)
                    ->where('pay_cycle_start_date', $start_date->format('Y-m-d'))
                    ->first();

                if ($existingPayroll && $existingPayroll->status == PayrollStatusTypes::LOCKED->value) {
                    continue;
                } else if ($existingPayroll && $existingPayroll->status != PayrollStatusTypes::LOCKED->value) {
                    // $existingPayroll->entries()->delete();
                    $existingPayroll->delete();
                }

                // if ($existingPayroll && $existingPayroll->status != 'Not Calculated') {
                //     continue;
                // } else if ($existingPayroll && ($existingPayroll->status == 'Not Calculated' || $existingPayroll->status == 'Calculated')) {
                //     $existingPayroll->entries()->delete();
                // }

                $payroll = $this->payrollCalculationRepository->calculateMonthlyPayroll($employee, [
                    'pay_frequency' => $pay_frequency,
                    'start_date'    => $start_date,
                    'end_date'      => $end_date,
                ]);

                $this->payrollCalculationRepository->updateEmployeeTaxCalculation($payroll, $end_date);

            } else if ($pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                $start_date = isset($data['payroll_week_start_date']) ? Carbon::parse($data['payroll_week_start_date']) : null;
                $end_date   = isset($data['payroll_week_end_date']) ? Carbon::parse($data['payroll_week_end_date']) : null;

                // if ($include_new_joiners == false && $start_date->format('Y-m-d') < Carbon::parse($employee->joined_date)->format('Y-m-d')) {
                //     continue;
                // }

                // if ($include_resignees == false && $end_date->format('Y-m-d') > Carbon::parse($employee->last_date)->format('Y-m-d')) {
                //     continue;
                // }

                $existingPayroll = Payroll::where('employee_id', $employee->id)
                    ->where('pay_cycle_start_date', $start_date->format('Y-m-d'))
                    ->first();

                if ($existingPayroll && $existingPayroll->status == PayrollStatusTypes::LOCKED->value) {
                    continue;
                } else if ($existingPayroll && $existingPayroll->status != PayrollStatusTypes::LOCKED->value) {
                    $existingPayroll->entries()->delete();
                }

                // if ($existingPayroll && $existingPayroll->status != 'Not Calculated') {
                //     continue;
                // } else if ($existingPayroll && ($existingPayroll->status == 'Not Calculated' || $existingPayroll->status == 'Calculated')) {
                //     $existingPayroll->entries()->delete();
                // }

                $payroll = $this->payrollCalculationRepository->calculateWeeklyPayroll($employee, [
                    'pay_frequency' => $pay_frequency,
                    'start_date'    => $start_date,
                    'end_date'      => $end_date,
                ]);

                $this->payrollCalculationRepository->updateEmployeeTaxCalculation($payroll, $end_date);
            } else if ($pay_frequency == PayFrequencyTypes::DAILY->value) {
                $daily_pay_dates = $data['payroll_dates'] ?? [];
                $months          = collect($daily_pay_dates)->map(function ($date) {
                    return Carbon::parse($date)->format('Y-m');
                });
                $start_date = $daily_pay_dates[0];
                $end_date   = $daily_pay_dates[count($daily_pay_dates) - 1];

                $payroll = $this->payrollCalculationRepository->calculateDailyPayroll($employee, [
                    'pay_frequency'   => $pay_frequency,
                    'daily_pay_dates' => $daily_pay_dates,
                    'start_date'      => $start_date,
                    'end_date'        => $end_date,
                ]);

                $this->payrollCalculationRepository->updateEmployeeTaxCalculation($payroll, $end_date);
            } else if ($pay_frequency == PayFrequencyTypes::HOURLY->value) {
                $hourly_pay_dates = $data['payroll_dates'] ?? [];
                $months           = collect($hourly_pay_dates)->map(function ($date) {
                    return Carbon::parse($date)->format('Y-m');
                });
                $start_date = $hourly_pay_dates[0];
                $end_date   = $hourly_pay_dates[count($hourly_pay_dates) - 1];

                $hours_source = $data['hours_source'];
                $total_hours  = $hours_source == PayrollHoursSourceTypes::MANUAL_INPUT ?
                $data['hours_worked_per_date'] * count($hourly_pay_dates)
                    : $this->payrollAttendanceService->getActualWorkingHoursWithinDates($employee, $hourly_pay_dates);

                $payroll = $this->payrollCalculationRepository->calculateHourlylyPayroll($employee, [
                    'pay_frequency'    => $pay_frequency,
                    'start_date'       => $start_date,
                    'end_date'         => $end_date,
                    'hourly_pay_dates' => $hourly_pay_dates,
                    'total_hours'      => $total_hours,
                ]);

                $this->payrollCalculationRepository->updateEmployeeTaxCalculation($payroll, $end_date);
            }

        }

        DB::commit();
    }

    public function recalculatePayrolls(array $payroll_ids)
    {
        $payrolls = $this->payrollRepository->findByMultipleIds($payroll_ids);

        DB::beginTransaction();

        foreach ($payrolls as $key => $payroll) {
            if ($payroll->employee?->payrollPolicy == null) {
                // throw new \Exception('Setup Payroll Policy for ' . $payroll->employee?->name, 400);
                abort(400, 'Setup Payroll Policy for ' . $payroll->employee?->name);
            }

            $employee = Employee::with(['payrollPolicy'])
                ->where('id', $payroll->employee_id)
                ->first();
            $start_date = null;
            $end_date   = null;

            if ($payroll->pay_frequency == PayFrequencyTypes::MONTHLY->value) {
                $payroll_month  = Carbon::parse($payroll->payroll_month)->format('Y-m');
                $month          = Carbon::createFromFormat('Y-m-d', $payroll_month . '-' . $employee->payrollPolicy?->pay_cycle_start_date);
                $pay_cycle_data = PayrollHelper::getPayCycleStartAndEnd($employee->payrollPolicy?->pay_cycle_start_date, $month);
                $start_date     = $pay_cycle_data['start_date'];
                $end_date       = $pay_cycle_data['end_date'];

                $existingPayroll = Payroll::where('employee_id', $employee->id)
                    ->where('pay_cycle_start_date', $start_date->format('Y-m-d'))
                    ->first();
                if ($existingPayroll) {
                    $existingPayroll->entries()->delete();
                }

                $updated_payroll = $this->payrollCalculationRepository->calculateMonthlyPayroll($employee, [
                    'pay_frequency' => $payroll->pay_frequency,
                    'start_date'    => $start_date,
                    'end_date'      => $end_date,
                ]);

                $this->payrollCalculationRepository->updateEmployeeTaxCalculation($updated_payroll, $end_date);

            } else if ($payroll->pay_frequency == PayFrequencyTypes::WEEKLY->value) {
                $start_date = isset($payroll->pay_cycle_start_date) ? Carbon::parse($payroll->pay_cycle_start_date) : null;
                $end_date   = isset($payroll->pay_cycle_end_date) ? Carbon::parse($payroll->pay_cycle_end_date) : null;

                $existingPayroll = Payroll::where('employee_id', $employee->id)
                    ->where('pay_cycle_start_date', $start_date->format('Y-m-d'))
                    ->first();

                if ($existingPayroll) {
                    $existingPayroll->entries()->delete();
                }

                $updated_payroll = $this->payrollCalculationRepository->calculateWeeklyPayroll($employee, [
                    'pay_frequency' => $payroll->pay_frequency,
                    'start_date'    => $start_date,
                    'end_date'      => $end_date,
                ]);

                $this->payrollCalculationRepository->updateEmployeeTaxCalculation($updated_payroll, $end_date);
            } else if ($payroll->pay_frequency == PayFrequencyTypes::DAILY->value) {
                $daily_pay_dates = $payroll->daily_pay_dates;
                $months          = collect($daily_pay_dates)->map(function ($date) {
                    return Carbon::parse($date)->format('Y-m');
                });
                $start_date = $daily_pay_dates[0];
                $end_date   = $daily_pay_dates[count($daily_pay_dates) - 1];

                $updated_payroll = $this->payrollCalculationRepository->calculateDailyPayroll($employee, [
                    'pay_frequency'   => $payroll->pay_frequency,
                    'daily_pay_dates' => $daily_pay_dates,
                    'start_date'      => $start_date,
                    'end_date'        => $end_date,
                ]);

                $this->payrollCalculationRepository->updateEmployeeTaxCalculation($updated_payroll, $end_date);
            } else if ($payroll->pay_frequency == PayFrequencyTypes::HOURLY->value) {
                $hourly_pay_dates = $payroll->hourly_pay_dates;
                $months           = collect($hourly_pay_dates)->map(function ($date) {
                    return Carbon::parse($date)->format('Y-m');
                });
                $start_date = $daily_pay_dates[0];
                $end_date   = $daily_pay_dates[count($daily_pay_dates) - 1];

                // $hours_source = $data['hours_source'];
                // $total_hours  = $hours_source == PayrollHoursSourceTypes::MANUAL_INPUT ?
                // $data['hours_worked_per_date'] * count($hourly_pay_dates)
                //     : $this->payrollAttendanceService->getActualWorkingHoursWithinDates($employee, $hourly_pay_dates);

                $payroll = $this->payrollCalculationRepository->calculateHourlylyPayroll($employee, [
                    'pay_frequency'    => $payroll->pay_frequency,
                    'start_date'       => $start_date,
                    'end_date'         => $end_date,
                    'hourly_pay_dates' => $hourly_pay_dates,
                    'total_hours'      => $payroll->total_hours_worked,
                ]);

                $this->payrollCalculationRepository->updateEmployeeTaxCalculation($payroll, $end_date);
            }
        }

        DB::commit();
    }

    public function lock($id)
    {
        return $this->payrollRepository->lock($id);
    }

    public function unlock($id)
    {
        return $this->payrollRepository->unlock($id);
    }

    public function lockMultiplePayrolls($ids)
    {
        return $this->payrollRepository->lockMultiplePayrolls($ids);
    }

    public function unlockMultiplePayrolls($ids)
    {
        return $this->payrollRepository->unlockMultiplePayrolls($ids);
    }

    public function sendMultiplePayrolls($ids)
    {
        $company    = Company::with(['logoFile'])->first();
        $logoFile   = $this->objectStorage->getFile($company->logoFile?->path);
        $mimeType   = $this->objectStorage->getMimeType($company->logoFile?->path);
        $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoFile);
        $payrolls   = $this->payrollRepository->findByMultipleIds($ids);

        foreach ($payrolls as $key => $payroll) {
            $target_month = Carbon::parse($payroll->payroll_month)->format('M Y');

            $noti_data = [
                'title'        => "Your Salary has been transferred",
                'description'  => "Your <b>salary</b> for <b>$target_month</b> has been transferred.",
                'type'         => 'payroll',
                'direction'    => 'profile_payroll',
                'event'        => 'created',
                'causer'       => auth()->user(),
                'subject'      => $payroll,
                'send_to'      => 'custom',
                'employee_ids' => [$payroll->employee_id],
            ];

            $this->notificationService->save($noti_data);
            $employee_work_email = $payroll->employee->email;

            try {
                if ($employee_work_email) {
                    // Mail::to($employee_work_email)->send(new SendPayrollMail($payroll, $logoBase64, $company->display_name));

                    $payroll->update([
                        'status' => 'Payslip Sent',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("payroll_email_send_error", ["message" => $e->getMessage()]);
                Log::error("payroll_email_send_error", ["message" => "Error in mail sending for sending payroll-slip code: " . $payroll->id]);
                throw $e;
            }
        }
    }
}
