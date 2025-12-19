<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\App\Exports\EmployeeTaxFileExport;
use Modules\Payroll\App\Http\Requests\EmployeeTaxFileRequest;
use Modules\Payroll\App\resources\EmployeeTaxFileDetailResource;
use Modules\Payroll\App\resources\EmployeeTaxFileResource;
use Modules\Payroll\App\Services\EmployeeTaxFileService;

class EmployeeTaxFileController extends Controller
{
    protected $service;

    public function __construct(EmployeeTaxFileService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $employee_tax_files = $this->service->findByParams($request->all());

        if ($request->export) {
            $format = strtolower($request->format) ?? 'excel';

            switch ($format) {
                case 'excel':
                    return Excel::download(new EmployeeTaxFileExport($employee_tax_files), 'employee_tax_files.xlsx');
                    break;
                case 'csv':
                    return Excel::download(new EmployeeTaxFileExport($employee_tax_files), 'employee_tax_files.csv');
                    break;
                default:
                    return Excel::download(new EmployeeTaxFileExport($employee_tax_files), 'employee_tax_files.xlsx');
                    break;
            }
        }

        return response()->json([
            'status'  => true,
            'data'    => [
                'table_view_id'      => TableView::FILING->value,
                'employee_tax_files' => $employee_tax_files,
            ],
            'message' => '',
        ], 200);
    }

    public function show($employee_Id)
    {
        $employee_tax_file = $this->service->findByEmployeeId($employee_Id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'employee_tax_file' => new EmployeeTaxFileDetailResource($employee_tax_file),
            ],
            'message' => '',
        ], 200);
    }

    public function getFilingRecords($employee_id, Request $request)
    {
        $filing_records = $this->service->findFilingRecordsByEmployeeId($employee_id, $request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
                'filing_records' => $filing_records,
            ],
            'message' => '',
        ], 200);
    }

    public function store(EmployeeTaxFileRequest $request, $employee_Id)
    {
        $this->service->saveOrUpdate($request->validated(), $employee_Id);

        $employee_tax_file = $this->service->findByEmployeeId($employee_Id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'employee_tax_file' => new EmployeeTaxFileResource($employee_tax_file),
            ],
            'message' => 'Success',
        ], 200);
    }
}
