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
use Modules\CRM\App\Http\Requests\CompanyImportRequest;
use Modules\CRM\App\Models\Tax;
use Modules\CRM\App\Services\CompanyService;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\Currency;
use Nnjeim\World\Models\State;

class CompanyImport implements WithHeadingRow, SkipsEmptyRows, ToCollection, SkipsOnFailure
{
    use SkipsFailures;
    protected $service;

    public function __construct(CompanyService $service) {
        $this->service = $service;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) 
        {
            $rules = (new CompanyImportRequest())->rules(); 
            $data = $row->toArray();

            $tax = Tax::where('name', $row['tax'])->whereNull('deleted_at')->first();
            $currency = Currency::where('name', $row['currency'])->whereNull('deleted_at')->first();
            $billing_country = Country::where('name', $row['billing_country'])->whereNull('deleted_at')->first();
            $billing_state = State::where('name', $row['billing_state'])->whereNull('deleted_at')->first();
            $shipping_country = Country::where('name', $row['shipping_country'])->whereNull('deleted_at')->first();
            $shipping_state = State::where('name', $row['shipping_state'])->whereNull('deleted_at')->first();

            $data['company_code'] = $row['code'];
            $data['name'] = $row['name'];
            $data['domain'] = $row['domain'];
            $data['email'] = $row['email'];
            $data['phone_dial_code'] = $row['phone_dial_code'];
            $data['phone_number'] = $row['phone_number'];
            $data['tax_id'] = $tax ? $tax->id : null;
            $data['currency_id'] = $currency ? $currency->id : null;
            $data['billing_country_id'] = $billing_country ? $billing_country->id : null;
            $data['billing_state_id'] = $billing_state ? $billing_state->id : null;
            $data['billing_district'] = $row['billing_district'] ?? null;
            $data['billing_zip_code'] = $row['billing_zip_code'] ?? null;
            $data['billing_address_line_1'] = $row['billing_address_line_1'] ?? null;
            $data['billing_address_line_2'] = $row['billing_address_line_2'] ?? null;
            $data['billing_phone_dial_code'] = $row['billing_phone_dial_code'] ?? null;
            $data['billing_phone_number'] = $row['billing_phone_number'] ?? null;
            $data['shipping_same_as_billing'] = isset($row['shipping_same_as_billing']) && $row['shipping_same_as_billing'] == 'yes' ? 1 : 0;
            $data['shipping_country_id'] = $shipping_country ? $shipping_country->id : null;
            $data['shipping_state_id'] = $shipping_state ? $shipping_state->id : null;
            $data['shipping_district'] = $row['shipping_district'] ?? null;
            $data['shipping_zip_code'] = $row['shipping_zip_code'] ?? null;
            $data['shipping_address_line_1'] = $row['shipping_address_line_1'] ?? null;
            $data['shipping_address_line_2'] = $row['shipping_address_line_2'] ?? null;
            $data['shipping_phone_dial_code'] = $row['shipping_phone_dial_code'] ?? null;
            $data['shipping_phone_number'] = $row['shipping_phone_number'] ?? null;
            $data['status'] = isset($row['status']) && $row['status'] == 'active' ? 1 : 0;

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
