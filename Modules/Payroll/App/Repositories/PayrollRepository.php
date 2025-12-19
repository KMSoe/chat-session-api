<?php
namespace Modules\Payroll\App\Repositories;

use Carbon\Carbon;
use function Aws\filter;
use Modules\Attendance\Services\PayrollAttendanceService;
use Modules\Leave\App\Repositories\LeaveRequestRepository;
use Modules\OverTime\App\Repositories\OverTimeRepository;
use Modules\Payroll\App\Enums\PayFrequencyTypes;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Modules\Payroll\App\Models\Payroll;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\resources\PayrollResource;
use Modules\Storage\App\Classes\ObjectStorage;

class PayrollRepository
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

    public function findByParams(array $params)
    {
        $search   = ! empty($params['search']) ? $params['search'] : '';
        $per_page = isset($params['per_page']) ? intval($params['per_page']) : 20;

        $department_ids = collect(explode(',', $params['departments'] ?? ''))
            ->filter(fn($id) => ! empty($id))
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $data = Payroll::with([
            'currency',
            'updatedBy:id,name',
            'employee'   => function ($query) {
                $query->with(['groups', 'departments', 'designations', 'location:id,name', 'branch:id,name', 'bankInfo:id,bank_name,bank_account_number'])
                    ->whereNull('deleted_at')
                    ->select('employees.id', 'employees.employee_code', 'employees.name');
            },
            'earnings'   => function ($query) {$query->orderBy('id');},
            'deductions' => function ($query) {$query->orderBy('id');},
            'overtimeSummary',
            'absentSummary',
            'unpaidLeaveSummary',
            'slip.file',
        ])
            ->whereHas('employee', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->where(function ($query) use ($params, $department_ids, $search) {
                // if (isset($params['month']) && ! empty($params['month'])) {
                //     $query->whereMonth('payroll_month', $params['month']);
                // }

                if (count($department_ids) > 0 && strtolower($params['departments'] ?? '') !== 'all') {
                    $query->whereHas('employee.departments', function ($q) use ($department_ids) {
                        $q->whereIn('departments.id', $department_ids);
                    });
                }

                if (isset($params['pay_frequency']) && ! empty($params['pay_frequency']) && $params['pay_frequency'] != 'all') {
                    $query->where('pay_frequency', $params['pay_frequency']);
                }

                if (isset($params['start_date']) && ! empty($params['start_date']) && isset($params['end_date']) && ! empty($params['end_date'])) {
                    $start_date = Carbon::parse($params['start_date']);
                    $end_date   = Carbon::parse($params['end_date']);

                    $query->where(function ($q) use ($start_date, $end_date) {
                        $q->where(function ($q2) use ($start_date, $end_date) {
                            $q2->where('pay_frequency', PayFrequencyTypes::MONTHLY->value)
                                ->where(function ($q3) use ($start_date, $end_date) {
                                    $q3->whereMonth('payroll_month', '=', $start_date->format('m'))
                                        ->orWhereMonth('payroll_month', '=', $end_date->format('m'));
                                });
                        })
                            ->orWhere(function ($q2) use ($start_date, $end_date) {
                                $q2->where('pay_frequency', PayFrequencyTypes::WEEKLY->value)
                                    ->where(function ($q3) use ($start_date, $end_date) {
                                        $q3->whereDate('pay_cycle_end_date', '>=', $start_date)
                                            ->whereDate('pay_cycle_start_date', '<=', $end_date);
                                    });
                            })
                            ->orWhere(function ($q2) use ($start_date, $end_date) {
                                $q2->where('pay_frequency', PayFrequencyTypes::DAILY->value)
                                    ->where(function ($q3) use ($start_date, $end_date) {
                                        $q3->whereJsonContains('daily_pay_dates', function ($date) use ($start_date, $end_date) {
                                            return Carbon::parse($date)->format('Y-m-d') >= $start_date->format('Y-m-d') ||
                                            Carbon::parse($date)->format('Y-m-d') <= $end_date->format('Y-m-d');
                                        });
                                    });
                            })
                            ->orWhere(function ($q2) use ($start_date, $end_date) {
                                $q2->where('pay_frequency', PayFrequencyTypes::HOURLY->value)
                                    ->where(function ($q3) use ($start_date, $end_date) {
                                        $q3->whereJsonContains('hourly_pay_dates', function ($date) use ($start_date, $end_date) {
                                            return Carbon::parse($date)->format('Y-m-d') >= $start_date->format('Y-m-d') ||
                                            Carbon::parse($date)->format('Y-m-d') <= $end_date->format('Y-m-d');
                                        });
                                    });
                            });
                    });
                }

                if ($search != '') {
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('employee', function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%$search%")
                                ->orWhere('employee_code', 'LIKE', "%$search%");
                        });
                    });
                }
            })
            ->orderBy('payrolls.updated_at', 'DESC');

        if (! empty($params['export']) && $params['export']) {
            if (! empty($params['only_this_page']) && $params['only_this_page']) {
                $data = $data->skip((($params['page'] ?? 1) - 1) * $per_page)->take($per_page)->get();
            } else {
                $data = $data->get();
            }

            $data = collect($data)->map(function ($item) {
                return new PayrollResource($item);
            });
        } else {
            $all_earnings   = PayrollComponent::where('component_type', PayrollComponentTypes::EARNING->value)->orderBy('id')->get();
            $all_deductions = PayrollComponent::where('component_type', PayrollComponentTypes::DEDUCTION->value)->orderBy('id')->get();

            $data = $data->paginate($per_page);

            $items = $data->getCollection();

            $items = collect($items)->map(function ($item) use ($all_earnings, $all_deductions) {
                $item->all_earnings   = $all_earnings;
                $item->all_deductions = $all_deductions;
                return new PayrollResource($item);
            });

            $data = $data->setCollection($items);
        }

        return $data;
    }

    public function findById($id)
    {
        return Payroll::with([
            'currency',
            'updatedBy:id,name',
            'employee' => function ($query) {
                $query->with(['groups', 'departments', 'designations', 'payrollPolicy']);
            },
            'earnings',
            'deductions',
            'leaveSummary',
            'overtimeSummary',
            'absentSummary',
            'unpaidLeaveSummary',
            'slip.file',
        ])->findOrFail($id);
    }

    public function findByMultipleIds($ids)
    {
        return Payroll::with([
            'currency',
            'updatedBy:id,name',
            'employee' => function ($query) {
                $query->with(['groups', 'departments', 'designations', 'payrollPolicy']);
            },
            'earnings',
            'deductions',
            'leaveSummary',
            'overtimeSummary',
            'absentSummary',
            'unpaidLeaveSummary',
            'slip.file',
        ])
            ->whereIn('id', $ids)
            ->get();
    }

    public function lock($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->update(['status' => 'Locked']);

        return $payroll;
    }

    public function unlock($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->update(['status' => 'Calculated']);

        return $payroll;
    }

    // public function downloadSlip($id)
    // {
    //     $company           = Company::with(['logoFile'])->first();
    //     $logoFile          = $this->objectStorage->getFile($company->logoFile?->path);
    //     $mimeType          = $this->objectStorage->getMimeType($company->logoFile?->path);
    //     $logoBase64        = 'data:' . $mimeType . ';base64,' . base64_encode($logoFile);
    //     $numberToWords     = new NumberToWords();
    //     $numberTransformer = $numberToWords->getNumberTransformer('en');

    //     $payroll = $this->findById($id);

    //     return $this->generateSpecificPayrollSlip($payroll, $company, $logoBase64, $numberTransformer);
    // }

    // public function generateSlip($id)
    // {
    //     $folder = Folder::where('name', 'payroll_slips')
    //         ->first();

    //     if (! $folder) {
    //         $folder = Folder::create([
    //             'name' => "payroll_slips",
    //         ]);
    //     }

    //     $company           = Company::with(['logoFile'])->first();
    //     $logoFile          = $this->objectStorage->getFile($company->logoFile?->path);
    //     $mimeType          = $this->objectStorage->getMimeType($company->logoFile?->path);
    //     $logoBase64        = 'data:' . $mimeType . ';base64,' . base64_encode($logoFile);
    //     $numberToWords     = new NumberToWords();
    //     $numberTransformer = $numberToWords->getNumberTransformer('en');

    //     $payroll = $this->findById($id);

    //     return $this->generateSpecificPayrollSlip($payroll, $company, $logoBase64, $numberTransformer);
    // }

    // public function generateMultiplePayrollSlips(array $ids)
    // {
    //     $folder = Folder::where('name', 'payroll_slips')
    //         ->first();

    //     if (! $folder) {
    //         $folder = Folder::create([
    //             'name' => "payroll_slips",
    //         ]);
    //     }

    //     $company           = Company::with(['logoFile'])->first();
    //     $logoFile          = $this->objectStorage->getFile($company->logoFile?->path);
    //     $mimeType          = $this->objectStorage->getMimeType($company->logoFile?->path);
    //     $logoBase64        = 'data:' . $mimeType . ';base64,' . base64_encode($logoFile);
    //     $numberToWords     = new NumberToWords();
    //     $numberTransformer = $numberToWords->getNumberTransformer('en');
    //     $payrolls          = $this->findByMultipleIds($ids);

    //     foreach ($payrolls as $key => $payroll) {
    //         $this->generateSpecificPayrollSlip($payroll, $company, $logoBase64, $numberTransformer, $folder);
    //     }
    // }

    // public function generateSpecificPayrollSlip($payroll, $company, $logoBase64, $numberTransformer)
    // {
    //     // if ($payroll->slip && $payroll->slip?->file_id) {
    //     //     File::where('id', $payroll->slip?->file_id)->delete();
    //     // }

    //     // PayrollSlip::where('payroll_id', $payroll->id)->delete();

    //     $payroll_slip_template = PayrollSlipTemplate::with(['earningComponents', 'deductionComponents'])
    //         ->join('employee_payroll_slip_templates', 'employee_payroll_slip_templates.payroll_slip_template_id', 'payroll_slip_templates.id')
    //         ->where('employee_payroll_slip_templates.employee_id', $payroll->employee_id)
    //         ->select('payroll_slip_templates.*')
    //         ->first();

    //     if (! $payroll_slip_template) {
    //         $payroll_slip_template = PayrollSlipTemplate::with(['earningComponents', 'deductionComponents'])
    //             ->where('is_default', true)->first();
    //     }

    //     if (! $payroll_slip_template) {
    //         throw new \Exception('Setup Payslip Design for' . $payroll->employee?->name);
    //     }

    //     // $slip = $payroll->slip()->create([
    //     //     'payslip_template_id' => $payroll_slip_template->id,
    //     //     'status'              => 'processing',
    //     // ]);

    //     $employee = $payroll->employee;

    //     $company_info_items  = [];
    //     $employee_info_items = [];
    //     $template_earnings   = [];
    //     $template_deductions = [];

    //     foreach ($payroll_slip_template->company_info_items as $key => $company_info_item) {
    //         $access_key = $company_info_item['access_key'];
    //         $value      = $access_key == 'logo' ? $this->objectStorage->getUrl($company->logoFile?->path) : $company->{$access_key};

    //         $company_info_items[] = [
    //             "label" => $access_key == 'logo' ? 'logo' : $company_info_item['label'],
    //             "value" => $value,
    //         ];
    //     }

    //     foreach ($payroll_slip_template->employee_info_items as $key => $employee_info_item) {
    //         $access_key = $employee_info_item['access_key'];
    //         $value      = '';

    //         if ($access_key == 'group') {
    //             $value = collect($employee->groups)->pluck('name')->implode(', ');
    //         } else if ($access_key == 'department') {
    //             $value = collect($employee->departments)->pluck('name')->implode(', ');
    //         } else if ($access_key == 'designation') {
    //             $value = collect($employee->designations)->pluck('name')->implode(', ');
    //         } else if ($access_key == 'bank_account_number') {
    //             $value = $employee->bankInfo?->bank_account_number;
    //         } else {
    //             $value = $employee->{$access_key};
    //         }

    //         $employee_info_items[] = [
    //             "label" => $employee_info_item['label'],
    //             "value" => $value,
    //         ];
    //     }

    //     $template_earnings = collect($payroll_slip_template->earningComponents)->map(function ($component) use ($payroll) {
    //         $amount = $payroll->earnings()->where('payroll_component_id', $component->id)->value('amount') ?? 0;

    //         return (object) [
    //             "id"              => $component->id,
    //             "name"            => $component->name,
    //             "amount"          => $amount,
    //             "affects_net_pay" => $component->affects_net_pay,
    //             "taxable"         => $component->taxable,
    //         ];
    //     })->filter(function ($component) use ($payroll_slip_template) {
    //         if ($payroll_slip_template->remove_components_with_zero_amount == true) {
    //             return $component->amount > 0;
    //         } else {
    //             return true;
    //         }
    //     });

    //     $template_deductions = collect($payroll_slip_template->deductionComponents)->map(function ($component) use ($payroll) {
    //         $amount = $payroll->deductions()->where('payroll_component_id', $component->id)->value('amount') ?? 0;

    //         return (object) [
    //             "id"              => $component->id,
    //             "name"            => $component->name,
    //             "amount"          => $amount,
    //             "affects_net_pay" => $component->affects_net_pay,
    //             "taxable"         => $component->taxable,
    //         ];
    //     })->filter(function ($component) use ($payroll_slip_template) {
    //         if ($payroll_slip_template->remove_components_with_zero_amount == true) {
    //             return $component->amount > 0;
    //         } else {
    //             return true;
    //         }
    //     });

    //     $default_earnings = collect([
    //         (object) [
    //             'name'   => 'Basic Salary',
    //             'amount' => $payroll->basic_salary,
    //         ],
    //         (object) [
    //             'name'   => 'OverTime Pay',
    //             'amount' => $payroll->overtimeSummary?->amount ?? 0,
    //         ],
    //     ])->filter(function ($item) use ($payroll_slip_template) {
    //         if ($payroll_slip_template->remove_components_with_zero_amount == true) {
    //             return $item->amount > 0;
    //         } else {
    //             return true;
    //         }
    //     })->values();

    //     $gross_salary     = $default_earnings->sum('amount') + $template_earnings->where('affects_net_pay', true)->sum('amount');
    //     $total_deductions = $template_deductions->where('affects_net_pay', true)->sum('amount');
    //     $net_pay          = $gross_salary - $total_deductions;

    //     $pdf = Pdf::loadView("payroll.pay-slip", [
    //         'logo'                  => $logoBase64,
    //         'company_name'          => $company->display_name,
    //         'company_info_items'    => $company_info_items,
    //         'employee_info_items'   => $employee_info_items,
    //         'payroll'               => $payroll,
    //         'default_earnings'      => $default_earnings,
    //         'template_earnings'     => $template_earnings,
    //         'template_deductions'   => $template_deductions,
    //         'leave_summary'         => $payroll->leaveSummary,
    //         // 'default_deductions'    => $default_deductions,
    //         // 'payroll_earnings'      => $payroll_slip_template->remove_components_with_zero_amount == false ? $payroll->earnings :
    //         // collect($payroll->earnings)->filter(function ($item) {return $item->amount != 0;}),
    //         // 'payroll_deductions'    => $payroll_slip_template->remove_components_with_zero_amount == false ? $payroll->deductions :
    //         // collect($payroll->deductions)->filter(function ($item) {return $item->amount != 0;}),
    //         'pay_period'            => Carbon::parse($payroll->payroll_month)->format('F Y'),
    //         'pay_date'              => Carbon::now()->format('d-m-Y'),
    //         'basic_salary'          => $payroll->basic_salary,
    //         'gross_salary'          => $gross_salary,
    //         'total_deductions'      => $total_deductions,
    //         'net_pay'               => $net_pay,
    //         'net_pay_in_words'      => ucfirst($numberTransformer->toWords($net_pay ?? 0)),
    //         'payroll_slip_template' => $payroll_slip_template,

    //     ]);

    //     return $pdf->stream();

    //     // $pdf->getDomPDF()->getOptions()->setIsRemoteEnabled(true);

    //     // $file_content = $pdf->output();

    //     // $name = strtolower(explode(" ", $payroll->employee->name)[0]) . '-' . $payroll->employee->employee_code . '-' . Carbon::parse($payroll->payroll_month)->format('MY') . '.pdf';

    //     // $path = $this->objectStorage->store($folder->name, $file_content, $name);
    //     // Storage::disk('s3')->put("$folder->name/$name", $pdf->output());

    //     // $file_record = File::create([
    //     //     'folder_id'   => $folder->id ?? null,
    //     //     'name'        => $name,
    //     //     'basename'    => $name,
    //     //     'description' => null,
    //     //     'path'        => "$folder->name/$name",
    //     //     'type'        => 'pdf',
    //     //     'extension'   => 'pdf',
    //     //     'size'        => strlen($file_content),
    //     //     'uploaded_by' => auth()->id(),
    //     // ]);

    //     // $slip->update([
    //     //     'file_id' => $file_record->id,
    //     //     'status'  => 'done',
    //     // ]);

    //     // $payroll->update([
    //     //     'status'   => 'Payslip Generated',
    //     //     'pay_date' => Carbon::now()->format('Y-m-d'),
    //     // ]);
    // }

    public function lockMultiplePayrolls($ids)
    {
        return Payroll::whereIn('id', $ids)->update([
            'status' => 'Locked',
        ]);
    }

    public function unlockMultiplePayrolls($ids)
    {
        return Payroll::whereIn('id', $ids)->update([
            'status' => 'Calculated',
        ]);
    }
}
