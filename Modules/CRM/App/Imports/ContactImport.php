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
use Modules\CRM\App\Http\Requests\ContactImportRequest;
use Modules\CRM\App\Models\Company;
use Modules\CRM\App\Services\ContactService;

class ContactImport implements WithHeadingRow, SkipsEmptyRows, ToCollection, SkipsOnFailure
{
    use SkipsFailures;
    protected $service;

    public function __construct(ContactService $service) {
        $this->service = $service;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) 
        {
            $rules = (new ContactImportRequest())->rules(); 
            $data = $row->toArray();

            $company = Company::where('id', $row['company_name'])->first();

            $data['contact_code'] = $row['id'];
            $data['first_name'] = $row['first_name'];
            $data['last_name'] = $row['last_name'];
            $data['email'] = $row['email'];
            $data['work_phone_dial_code'] = $row['work_phone_dial_code'];
            $data['work_phone_number'] = $row['work_phone_number'];
            $data['mobile_phone_dial_code'] = $row['mobile_phone_dial_code'];
            $data['mobile_phone_number'] = $row['mobile_phone_number'];
            $data['is_customer'] = $row['is_customer'] == 'yes' ? 1 : 0;
            $data['company_id'] = $company ? $company->id : null;
            $data['status'] = $row['status'] == 'active' ? 1 : 0;
            $data['password'] = $row['password'] ?? null;

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
