<?php
namespace Modules\Payroll\App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Employee\App\Models\Employee;
use Modules\Payroll\App\Services\AdwOpeningBalanceService;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AdwOpeningBalanceImport implements WithStartRow, WithHeadingRow, ToCollection, WithChunkReading, WithValidation, SkipsEmptyRows
{
    private $service;

    public function __construct(AdwOpeningBalanceService $service)
    {
        $this->service = $service;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'employee'                    => 'required|string|exists:employees,name',
            'effective_date'              => 'required|date',
            'from_date'                   => 'required|date',
            'to_date'                     => 'required|date|after_or_equal:from_date',
            'total_wages_in_period'       => 'required|numeric|min:0',
            'total_worked_days_in_period' => 'required|integer|min:1',
            'adjustment_reason'           => 'nullable|string',
        ];
    }

    public function collection(Collection $rows)
    {
        $user = auth()->user();

        foreach ($rows as $row) {
            $employee = Employee::where('name', $row['employee'])->first();

            $data = [
                'employee_id'                 => $employee->id,
                'effective_date'              => $this->parseDate($row['effective_date']),
                'from_date'                   => $this->parseDate($row['from_date']),
                'to_date'                     => $this->parseDate($row['to_date']),
                'total_wages_in_period'       => $row['total_wages_in_period'],
                'total_worked_days_in_period' => $row['total_worked_days_in_period'],
                'adjustment_reason'           => $row['adjustment_reason'] ?? null,
                'created_by'                  => $user->id, 
            ];

            $this->service->create($data);
        }
    }

    /**
     * Parse date from Excel (handles both serial dates and text dates)
     */
    private function parseDate($dateValue): Carbon
    {
        if (is_numeric($dateValue)) {
            // Excel serial date
            return Carbon::instance(Date::excelToDateTimeObject($dateValue));
        } else {
            // Text date
            return Carbon::parse($dateValue);
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
