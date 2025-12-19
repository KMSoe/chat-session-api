<?php
namespace Modules\Payroll\App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EmployeePayrollComponentExport implements FromView, ShouldAutoSize
{
    protected $items;
    protected $payroll_components;

    public function __construct($items, $payroll_components)
    {
        $this->items              = $items;
        $this->payroll_components = $payroll_components;
    }

    public function view(): View
    {
        $items              = $this->items;
        $payroll_components = $this->payroll_components;

        return view('payroll::exports.employee_payroll_conponent', compact('items', 'payroll_components'));
    }
}
