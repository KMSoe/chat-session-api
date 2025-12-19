<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\App\Enums\PayFrequencyTypes;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Modules\Payroll\App\Exports\PayrollExport;
use Modules\Payroll\App\Http\Requests\CalculatePayrollRequest;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\Models\PayrollPolicy;
use Modules\Payroll\App\resources\PayrollDetailResource;
use Modules\Payroll\App\Services\PayrollService;
use Modules\Payroll\App\Services\PayrollSlipService;
use Modules\Storage\App\Classes\ObjectStorage;
use Modules\Storage\App\Interfaces\StorageInterface;

class PayrollController extends Controller
{
    protected $payrollService;
    protected PayrollSlipService $payrollSlipService;
    protected StorageInterface $storage;

    public function __construct(
        PayrollService $payrollService,
        PayrollSlipService $payrollSlipService,
        ObjectStorage $storage) {
        $this->payrollService     = $payrollService;
        $this->payrollSlipService = $payrollSlipService;
        $this->storage            = $storage;
    }

    public function index(Request $request)
    {
        $payrolls = $this->payrollService->findByParams($request->all());

        if ($request->export) {
            $all_earnings   = PayrollComponent::where('component_type', PayrollComponentTypes::EARNING->value)->orderBy('id')->get();
            $all_deductions = PayrollComponent::where('component_type', PayrollComponentTypes::DEDUCTION->value)->orderBy('id')->get();
            $format         = strtolower($request->format) ?? 'excel';

            switch ($format) {
                case 'excel':
                    return Excel::download(new PayrollExport($payrolls, $all_earnings, $all_deductions), 'payrolls.xlsx');
                    break;
                case 'csv':
                    return Excel::download(new PayrollExport($payrolls, $all_earnings, $all_deductions), 'payrolls.csv');
                    break;
                default:
                    return Excel::download(new PayrollExport($payrolls, $all_earnings, $all_deductions), 'payrolls.xlsx');
                    break;
            }
        }

        return response()->json([
            'status'  => true,
            'data'    => [
                'table_view_id' => TableView::PAYROLL->value,
                'payrolls'      => $payrolls,
            ],
            'message' => '',
        ], 200);
    }

    public function getPageData()
    {
        return response()->json([
            'status'  => true,
            'data'    => [
                'pay_frequencies'  => PayFrequencyTypes::values(),
                'payroll_policies' => PayrollPolicy::get(),
            ],
            'message' => '',
        ], 200);
    }

    public function show($id)
    {
        $payroll = $this->payrollService->findById($id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll' => new PayrollDetailResource($payroll),
            ],
            'message' => '',
        ], 200);
    }

    public function store(CalculatePayrollRequest $request)
    {
        $this->payrollService->calculatePayrolls($request->toArray());

        return response()->json([
            'status'  => true,
            'data'    => [

            ],
            'message' => 'Calculated',
        ], 201);
    }

    public function lock($id)
    {
        $this->payrollService->lock($id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll' => new PayrollDetailResource($this->payrollService->findById($id)),
            ],
            'message' => 'Payroll locked successfully.',
        ]);
    }

    public function unlock($id)
    {
        $this->payrollService->unlock($id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll' => new PayrollDetailResource($this->payrollService->findById($id)),
            ],
            'message' => 'Payroll unlocked successfully.',
        ]);
    }

    public function previewSlip($id)
    {
        return $this->payrollSlipService->previewSlip($id);
    }

    public function downloadSlip($id)
    {
        return $this->payrollSlipService->downloadSlip($id);
    }

    public function sendSlip($id)
    {
        $this->payrollSlipService->sendSlip($id);

        return response()->json([
            'status'  => true,
            'data'    => [],
            'message' => 'Payslip sent',
        ], 200);
    }

    public function sendMultiplePayrolls(Request $request)
    {
        $request->validate([
            'ids'   => "array|min:1",
            'ids.*' => "exists:payrolls,id",
        ]);

        $this->payrollSlipService->sendMultiplePayrolls($request->ids);

        return response()->json([
            'status'  => true,
            'data'    => [],
            'message' => 'Success',
        ], 200);
    }

    public function recalculateMultiplePayrolls(Request $request)
    {
        $request->validate([
            'ids'   => "array|min:1",
            'ids.*' => "exists:payrolls,id",
        ]);

        $this->payrollService->recalculatePayrolls($request->ids);

        return response()->json([
            'status'  => true,
            'data'    => [],
            'message' => 'Success',
        ], 200);
    }

    public function lockMultiplePayrolls(Request $request)
    {
        $request->validate([
            'ids'   => "array|min:1",
            'ids.*' => "exists:payrolls,id",
        ]);

        $this->payrollService->lockMultiplePayrolls($request->ids);

        return response()->json([
            'status'  => true,
            'data'    => [],
            'message' => 'Success',
        ], 200);
    }

    public function unlockMultiplePayrolls(Request $request)
    {
        $request->validate([
            'ids'   => "array|min:1",
            'ids.*' => "exists:payrolls,id",
        ]);

        $this->payrollService->unlockMultiplePayrolls($request->ids);

        return response()->json([
            'status'  => true,
            'data'    => [],
            'message' => 'Success',
        ], 200);
    }

    public function generateMultiplePayrolls(Request $request)
    {
        $request->validate([
            'ids'   => "array|min:1",
            'ids.*' => "exists:payrolls,id",
        ]);

        $this->payrollService->generateMultiplePayrollSlips($request->ids);

        return response()->json([
            'status'  => true,
            'data'    => [],
            'message' => 'Success',
        ], 200);
    }

}
