<?php

namespace Modules\CRM\App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TaskExport implements FromView, ShouldAutoSize
{
    protected $tasks;

    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }

    public function view(): View
    {
        $tasks = $this->tasks;

        return view('crm::exports.task', compact('tasks'));
    }
}
