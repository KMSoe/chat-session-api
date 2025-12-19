<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\App\Exports\EmployeePayrollComponentExport;
use Modules\Payroll\App\Http\Requests\UpdateEmployeePayrollComponentRequest;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\Services\EmployeePayrollComponentService;

class EmployeePayrollComponentController extends Controller
{
    protected $service;

    public function __construct(EmployeePayrollComponentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $employees = $this->service->findByParams($request->all());

        if ($request->export) {
            $payroll_components = PayrollComponent::where('component_scope_mode', 'Custom Amount per Employee')
                ->orderBy('id')
                ->get();

            $format = strtolower($request->format) ?? 'excel';

            switch ($format) {
                case 'excel':
                    return Excel::download(new EmployeePayrollComponentExport($employees, $payroll_components), 'payroll_definition.xlsx');
                    break;
                case 'csv':
                    return Excel::download(new EmployeePayrollComponentExport($employees, $payroll_components), 'payroll_definition.csv');
                    break;
                default:
                    return Excel::download(new EmployeePayrollComponentExport($employees, $payroll_components), 'payroll_definition.xlsx');
                    break;
            }
        }

        return response()->json([
            'status'  => true,
            'data'    => [
                'table_view_id' => TableView::PAYROLL_DEFINITION->value,
                'employees'     => $employees,
            ],
            'message' => '',
        ], 200);
    }

    public function updateEmployeePayrollComponents(UpdateEmployeePayrollComponentRequest $request)
    {
        $this->service->updateEmployeePayrollComponents($request->validated());

        return response()->json([
            'status'  => true,
            'data'    => [],
            'message' => 'Payroll components updated successfully',
        ], 200);
    }
}
