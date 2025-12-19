<?php

namespace Modules\CRM\App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CompanyExport implements FromView, ShouldAutoSize
{
    protected $companies;

    public function __construct($companies)
    {
        $this->companies = $companies;
    }

    public function view(): View
    {
        $companies = $this->companies;

        return view('crm::exports.company', compact('companies'));
    }
}
