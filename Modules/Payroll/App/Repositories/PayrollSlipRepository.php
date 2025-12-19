<?php
namespace Modules\Payroll\App\Repositories;

use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Payroll\App\Emails\SendPayrollMail;
use Modules\Payroll\App\Enums\PayFrequencyTypes;
use Modules\Payroll\App\Models\PayrollSlipTemplate;
use Modules\Payroll\App\Repositories\PayrollRepository;
use Modules\Storage\App\Classes\ObjectStorage;
use NumberToWords\NumberToWords;

class PayrollSlipRepository
{
    public function getCompanyInfoItems($company, $payroll_slip_template)
    {
        $company_info_items = [];

        foreach ($payroll_slip_template->company_info_items as $key => $company_info_item) {
            $access_key = $company_info_item['access_key'];
            $value      = $company->{$access_key};

            $company_info_items[] = [
                "label" => $access_key == 'logo' ? 'logo' : $company_info_item['label'],
                "value" => $value,
            ];
        }

        return $company_info_items;
    }

    public function getEmployeeInfoItems($employee, $payroll_slip_template)
    {
        $employee_info_items = [];

        foreach ($payroll_slip_template->employee_info_items as $key => $employee_info_item) {
            $access_key = $employee_info_item['access_key'];
            $value      = '';

            if ($access_key == 'group') {
                $value = collect($employee->groups)->pluck('name')->implode(', ');
            } else if ($access_key == 'department') {
                $value = collect($employee->departments)->pluck('name')->implode(', ');
            } else if ($access_key == 'designation') {
                $value = collect($employee->designations)->pluck('name')->implode(', ');
            } else if ($access_key == 'bank_account_number') {
                $value = $employee->bankInfo?->bank_account_number;
            } else if ($access_key == 'bank_name') {
                $value = $employee->bankInfo?->bank_name;
            } else {
                $value = $employee->{$access_key};
            }

            $employee_info_items[] = [
                "label" => $employee_info_item['label'],
                "value" => $value,
            ];
        }

        return $employee_info_items;
    }

    public function getPayrollDataForPrint($payroll, $company, $logoBase64)
    {
        $payroll_slip_template = PayrollSlipTemplate::with(['earningComponents', 'deductionComponents'])
            ->join('employee_payroll_slip_templates', 'employee_payroll_slip_templates.payroll_slip_template_id', 'payroll_slip_templates.id')
            ->where('employee_payroll_slip_templates.employee_id', $payroll->employee_id)
            ->select('payroll_slip_templates.*')
            ->first();

        if (! $payroll_slip_template) {
            $payroll_slip_template = PayrollSlipTemplate::with(['earningComponents', 'deductionComponents'])
                ->where('is_default', true)->first();
        }

        if (! $payroll_slip_template) {
            // throw new \Exception('Setup Payslip Design for ' . $payroll->employee?->name, 400);
            abort(400, 'Setup Payslip Design for ' . $payroll->employee?->name);
        }

        $template_earnings   = [];
        $template_deductions = [];

        $template_earnings = collect($payroll_slip_template->earningComponents)->map(function ($component) use ($payroll) {
            $amount = $payroll->earnings()->where('payroll_component_id', $component->id)->value('amount') ?? 0;

            return (object) [
                "id"              => $component->id,
                "name"            => $component->name,
                "amount"          => $amount,
                "affects_net_pay" => $component->affects_net_pay,
                "taxable"         => $component->taxable,
            ];
        })->filter(function ($component) use ($payroll_slip_template) {
            if ($payroll_slip_template->remove_components_with_zero_amount == true) {
                return $component->amount > 0;
            } else {
                return true;
            }
        });

        $template_deductions = collect($payroll_slip_template->deductionComponents)->map(function ($component) use ($payroll) {
            $amount = $payroll->deductions()->where('payroll_component_id', $component->id)->value('amount') ?? 0;

            return (object) [
                "id"              => $component->id,
                "name"            => $component->name,
                "amount"          => $amount,
                "affects_net_pay" => $component->affects_net_pay,
                "taxable"         => $component->taxable,
            ];
        })->filter(function ($component) use ($payroll_slip_template) {
            if ($payroll_slip_template->remove_components_with_zero_amount == true) {
                return $component->amount > 0;
            } else {
                return true;
            }
        });

        // $default_earnings = collect([
        //     $payroll->pay_frequency == PayFrequencyTypes::MONTHLY->value ?
        //     (object) [
        //         'name'   => 'Basic Salary',
        //         'amount' => $payroll->basic_salary,
        //     ] : (object) [
        //         'name'   => 'Pay Amount',
        //         'amount' => $payroll->pay_amount,
        //     ],
        //     (object) [
        //         'name'   => 'OverTime Pay',
        //         'amount' => $payroll->overtimeSummary?->amount ?? 0,
        //     ],
        // ])->filter(function ($item) use ($payroll_slip_template) {
        //     if ($payroll_slip_template->remove_components_with_zero_amount == true) {
        //         return $item->amount > 0;
        //     } else {
        //         return true;
        //     }
        // })->values();

        // $gross_salary      = $default_earnings->sum('amount') + $template_earnings->where('affects_net_pay', true)->sum('amount');
        $gross_salary      = $template_earnings->where('affects_net_pay', true)->sum('amount');
        $total_deductions  = $template_deductions->where('affects_net_pay', true)->sum('amount');
        $net_pay           = $gross_salary - $total_deductions;
        $numberToWords     = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('en');
        $net_pay_in_words  = ucfirst($numberTransformer->toWords($net_pay ?? 0));

        return [
            'logo'                  => $logoBase64,
            'company_name'          => $company->display_name,
            'company_info_items'    => $this->getCompanyInfoItems($company, $payroll_slip_template),
            'employee_info_items'   => $this->getEmployeeInfoItems($payroll->employee, $payroll_slip_template),
            'payroll'               => $payroll,
            'default_earnings'      => [],
            'template_earnings'     => $template_earnings,
            'template_deductions'   => $template_deductions,
            'leave_summary'         => $payroll->leaveSummary,
            'pay_period'            => $payroll->pay_period,
            'pay_date'              => $payroll->pay_date ? Carbon::parse()->format('d F Y') : '',
            'basic_salary'          => $payroll->basic_salary,
            'pay_amount'            => $payroll->pay_amount,
            'gross_salary'          => $gross_salary,
            'total_deductions'      => $total_deductions,
            'net_pay'               => $net_pay,
            'net_pay_in_words'      => $net_pay_in_words,
            'payroll_slip_template' => $payroll_slip_template,

        ];
    }

    public function previewSlip($id)
    {
        $company    = Company::with(['logoFile'])->first();
        $logoFile   = app(ObjectStorage::class)->getFile($company->logoFile?->path ?? '');
        $mimeType   = app(ObjectStorage::class)->getMimeType($company->logoFile?->path ?? '');
        $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoFile);

        $payroll      = app(PayrollRepository::class)->findById($id);
        $payroll_data = $this->getPayrollDataForPrint($payroll, $company, $logoBase64);

        $pdf = Pdf::loadView("payroll.pay-slip", $payroll_data);

        return $pdf->stream();
    }

    public function downloadSlip($id)
    {
        $company    = Company::with(['logoFile'])->first();
        $logoFile   = app(ObjectStorage::class)->getFile($company->logoFile?->path);
        $mimeType   = app(ObjectStorage::class)->getMimeType($company->logoFile?->path);
        $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoFile);

        $payroll      = app(PayrollRepository::class)->findById($id);
        $payroll_data = $this->getPayrollDataForPrint($payroll, $company, $logoBase64);

        $pdf = Pdf::loadView("payroll.pay-slip", $payroll_data);

        return $pdf->download();
    }

    public function getCompanyInfo()
    {
        $company    = Company::with(['logoFile'])->first();
        $logoFile   = app(ObjectStorage::class)->getFile($company->logoFile?->path);
        $mimeType   = app(ObjectStorage::class)->getMimeType($company->logoFile?->path);
        $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoFile);

        return [
            'company'    => $company,
            'logoBase64' => $logoBase64,
        ];
    }

    public function storeSlip()
    {

    }

    public function sendSlip($payroll, $company, $logoBase64)
    {
        $payroll_data = $this->getPayrollDataForPrint($payroll, $company, $logoBase64);

        try {
            Mail::to($payroll->employee?->email)->send(new SendPayrollMail($payroll_data, $logoBase64, $company));

            $payroll->update([
                'status'   => 'Payslip Sent',
                'pay_date' => Carbon::now()->format('Y-m-d'),
            ]);
        } catch (\Exception $e) {
            Log::error("payroll_email_send_error", ["message" => $e->getMessage()]);
            Log::error("payroll_email_send_error", ["message" => "Error in mail sending for sending payroll-slip code: " . $payroll->id]);
            throw $e;
        }
    }
}
