<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Http\Requests\EmployeeEnrollmentRequest;
use Modules\Payroll\App\resources\EmployeeEnrollmentResource;
use Modules\Payroll\App\Services\EmployeeEnrollmentService;

class EmployeeEnrollmentController extends Controller
{
    protected $service;

    public function __construct(EmployeeEnrollmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $employee_enrollments = $this->service->findByParams($request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
                'employee_enrollments' => $employee_enrollments,
            ],
            'message' => '',
        ], 200);
    }

    public function show($employee_Id)
    {
        $employee_enrollment = $this->service->findByEmployeeId($employee_Id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'employee_enrollment' => new EmployeeEnrollmentResource($employee_enrollment),
            ],
            'message' => '',
        ], 200);
    }

    public function store(EmployeeEnrollmentRequest $request, $employee_Id)
    {
        $this->service->saveOrUpdate($request->validated(), $employee_Id);

        $employee_enrollment = $this->service->findByEmployeeId($employee_Id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'employee_enrollment' => new EmployeeEnrollmentResource($employee_enrollment),
            ],
            'message' => 'Success',
        ], 200);
    }
}
