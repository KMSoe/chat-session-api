<?php
namespace Modules\Payroll\App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PayrollExport implements FromView, ShouldAutoSize
{
    protected $items;
    protected $all_earnings;
    protected $all_deductions;

    public function __construct($items, $all_earnings, $all_deductions)
    {
        $this->items          = $items;
        $this->all_earnings   = $all_earnings;
        $this->all_deductions = $all_deductions;
    }

    public function view(): View
    {
        $items          = $this->items;
        $all_earnings   = $this->all_earnings;
        $all_deductions = $this->all_deductions;

        return view('payroll::exports.payroll', compact('items', 'all_earnings', 'all_deductions'));
    }
}
