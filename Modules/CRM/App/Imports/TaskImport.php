<?php

namespace Modules\CRM\App\Imports;

use App\Models\User;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;
use Modules\CRM\App\Http\Requests\TaskImportRequest;
use Modules\CRM\App\Models\Project;
use Modules\CRM\App\Models\ProjectTaskStatus;
use Modules\CRM\App\Services\TaskService;

class TaskImport implements WithHeadingRow, SkipsEmptyRows, ToCollection, SkipsOnFailure
{
    use SkipsFailures;
    protected $service;

    public function __construct(TaskService $service) {
        $this->service = $service;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) 
        {
            $rules = (new TaskImportRequest())->rules(); 
            $data = $row->toArray();

            $data['title'] = $row['title'];

            $project = Project::where('name', $row['project'])->first();
            $data['project_id'] = $project?->id;

            if ($project && !empty($row['status'])) {
                $status = ProjectTaskStatus::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'name' => $row['status']
                    ],
                    [
                        'color' => '#6366f1',
                        'sort_order' => ProjectTaskStatus::where('project_id', $project->id)->max('sort_order') + 1
                    ]
                );
                $data['status_id'] = $status->id;
            } else {
                $data['status_id'] = null;
            }

            $data['due_date'] = $row['due_date'] ?? null;
            $data['sort_order'] = 1;
            $data['is_active'] = isset($row['is_active']) && strtolower($row['is_active']) == 'yes' ? 1 : 0;
            $data['created_by'] = auth()->user()->id;

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) 
            {
                  foreach ($validator->errors()->messages() as $field => $messages) 
                  {
                    $this->onFailure(new Failure(
                        $index + 2,
                        $field,      
                        $messages,
                        $row->toArray()
                    ));
                  }
                continue;
            }

            $this->service->create($data);
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize():int
    {
        return 100;
    }    
}
