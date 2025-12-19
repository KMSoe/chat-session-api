<?php

namespace Modules\CRM\App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProjectExport implements FromView, ShouldAutoSize
{
    protected $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;
    }

    public function view(): View
    {
        $projects = $this->projects;

        return view('crm::exports.project', compact('projects'));
    }
}
