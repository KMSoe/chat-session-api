<?php 

namespace Modules\CRM\App\Imports;

use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;
use Modules\CRM\App\Http\Requests\ProjectImportRequest;
use Modules\CRM\App\Models\ProjectStatus;
use Modules\CRM\App\Services\ProjectService;
use Modules\Employee\App\Models\Department;
use Modules\Employee\App\Models\Designation;
use Modules\Employee\App\Models\Employee;
use Modules\Employee\App\Models\Group;

class ProjectImport implements WithHeadingRow, SkipsEmptyRows, ToCollection, SkipsOnFailure
{
    use SkipsFailures;
    protected $service;

    public function __construct(ProjectService $service) {
        $this->service = $service;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) 
        {
            $rules = (new ProjectImportRequest())->rules(); 
            $data = $row->toArray();

            $data['project_code'] = $row['id'];
            $data['name'] = $row['name'];
            $data['description'] = $row['description'];
            $owner = Employee::where('name', $row['owner'])->first();
            $data['owner_id'] = $owner?->id;
            $data['start_date'] = $row['start_date'];
            $data['end_date'] = $row['end_date'];
            $data['is_active'] = $row['is_active'] == 'yes' ? 1 : 0;
            $data['visibility'] = strtolower($row['visibility']);
            $data['created_by'] = auth()->id();

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
