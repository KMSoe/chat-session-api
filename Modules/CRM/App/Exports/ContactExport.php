<?php

namespace Modules\CRM\App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ContactExport implements FromView, ShouldAutoSize
{
    protected $contacts;

    public function __construct($contacts)
    {
        $this->contacts = $contacts;
    }

    public function view(): View
    {
        $contacts = $this->contacts;

        return view('crm::exports.contact', compact('contacts'));
    }
}
