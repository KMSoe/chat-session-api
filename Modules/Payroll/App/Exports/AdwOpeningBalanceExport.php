<?php

namespace Modules\Payroll\App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AdwOpeningBalanceExport implements FromView, ShouldAutoSize
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function view(): View
    {
        $items = $this->items;

        return view('payroll::exports.adw_opening_balance', compact('items'));
    }
}
