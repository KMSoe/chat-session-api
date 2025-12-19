<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\resources\EmployeeTaxCalculationIncomeDetailResource;
use Modules\Payroll\App\resources\EmployeeTaxCalculationResource;
use Modules\Payroll\App\resources\ResidentialAddressDetailsResource;
use Modules\Payroll\App\Services\EmployeeTaxCalculationService;

class EmployeeTaxCalculationController extends Controller
{
    protected $service;

    public function __construct(EmployeeTaxCalculationService $service)
    {
        $this->service = $service;
    }

    public function index($tax_calculation_id, Request $request)
    {
        $employees = $this->service->findByParams($tax_calculation_id, $request->all());

        return response()->json([
            "status"  => true,
            "data"    => [
                "employees" => $employees,
            ],
            "message" => "",
        ], 200);
    }

    public function show($tax_calculation_id, $employee_id)
    {
        $employee = $this->service->findByEmployeeId($tax_calculation_id, $employee_id);

        return response()->json([
            "status"  => true,
            "data"    => [
                "employee" => new EmployeeTaxCalculationResource($employee),
            ],
            "message" => "",
        ], 200);
    }

    public function store($tax_calculation_id, Request $request)
    {
        $request->validate([
            'employee_ids'   => 'present|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $request->merge([
            'tax_calculation_id' => $tax_calculation_id,
        ]);

        $this->service->store($request->toArray());

        return response()->json([
            "status"  => true,
            "data"    => [
            ],
            "message" => "Success",
        ], 201);
    }

    public function updateLockStatus($tax_calculation_id, $employee_id, Request $request)
    {
        $this->service->updateLockStatus($tax_calculation_id, $employee_id);

        return response()->json([
            "status"  => true,
            "data"    => [
                "employee" => new EmployeeTaxCalculationResource($this->service->findByEmployeeId($tax_calculation_id, $employee_id)),
            ],
            "message" => "Updated",
        ], 200);
    }

    public function getResidentialDetails($tax_calculation_id, $employee_id, Request $request)
    {
        $residential_address_details = $this->service->getResidentialDetails($employee_id);

        return response()->json([
            "status"  => true,
            "data"    => [
                "residential_address_details" => new ResidentialAddressDetailsResource($residential_address_details),
            ],
            "message" => "",
        ], 200);
    }

    public function getIncomeDetails($tax_calculation_id, $employee_id, Request $request)
    {
        $income_details = $this->service->getIncomeDetails($tax_calculation_id, $employee_id);

        return response()->json([
            "status"  => true,
            "data"    => [
                "income_details" => EmployeeTaxCalculationIncomeDetailResource::collection($income_details),
            ],
            "message" => "",
        ], 200);
    }

    public function updateIncomeDetails($tax_calculation_id, $employee_id, Request $request)
    {
        $request->validate([
            'data'                               => 'present|array',
            'data.*.tax_form_income_category_id' => 'exists:tax_form_income_categories,id',
            'data.*.amount'                      => "numeric|min:0",
        ]);

        $this->service->updateIncomeDetails($tax_calculation_id, $employee_id, $request->toArray());

        $income_details = $this->service->getIncomeDetails($tax_calculation_id, $employee_id);

        return response()->json([
            "status"  => true,
            "data"    => [
                "income_details" => EmployeeTaxCalculationIncomeDetailResource::collection($income_details),
            ],
            "message" => "Updated",
        ], 200);
    }

    public function getOtherDetails($tax_calculation_id, $employee_id, Request $request)
    {
        $other_details = $this->service->getOtherDetails($tax_calculation_id, $employee_id);

        return response()->json([
            "status"  => true,
            "data"    => [
                "other_details" => $other_details,
            ],
            "message" => "",
        ], 200);
    }

    public function updateOtherDetails($tax_calculation_id, $employee_id, Request $request)
    {
        $request->validate([
            'wholly_or_partly_paid_either' => 'required|boolean',

            // Conditional validation
            'non_hong_kong_company_name'   => 'nullable|required_if:wholly_or_partly_paid_either,true|string|max:255',
            'address'                      => 'nullable|required_if:wholly_or_partly_paid_either,true|string|max:255',
            'amount'                       => 'nullable|required_if:wholly_or_partly_paid_either,true|numeric|min:0',
            'remarks'                      => 'nullable|required_if:wholly_or_partly_paid_either,true|string|nullable|max:1000',
        ]);

        $this->service->updateOtherDetails($tax_calculation_id, $employee_id, $request->toArray());

        $other_details = $this->service->getOtherDetails($tax_calculation_id, $employee_id);

        return response()->json([
            "status"  => true,
            "data"    => [
                "other_details" => $other_details,
            ],
            "message" => "Updated",
        ], 200);
    }

    public function destroy($tax_calculation_id, $employee_id)
    {
        $this->service->deleteByEmployeeId($tax_calculation_id, $employee_id);

        return response()->json([], 204);
    }

    public function bulkDelete(Request $request, $tax_calculation_id)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:employees,id',
        ]);

        $this->service->bulkDdeleteByEmployeeIds($tax_calculation_id, $request->ids);

        return response()->json([], 204);
    }
}
