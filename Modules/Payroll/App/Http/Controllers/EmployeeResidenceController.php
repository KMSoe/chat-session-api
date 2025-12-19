<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Http\Requests\EmployeeResidenceRequest;
use Modules\Payroll\App\Services\EmployeeResidenceService;

class EmployeeResidenceController extends Controller
{
    protected $service;

    public function __construct(EmployeeResidenceService $service)
    {
        $this->service = $service;
    }

    public function index($employee_id, Request $request)
    {
        $request->merge(['employee_id' => $employee_id]);

        $residences = $this->service->findByParams($request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
                'residences' => $residences,
            ],
            'message' => "",
        ], 200);
    }

    public function show($employee_id, $id)
    {
        return response()->json([
            'status'  => true,
            'data'    => [
                'residence' => $this->service->findById($id),
            ],
            'message' => "Success",
        ], 200);
    }

    public function store($employee_id, EmployeeResidenceRequest $request)
    {
        $request->merge(['employee_id' => $employee_id]);

        $residence = $this->service->create($request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
                'residence' => $residence,
            ],
            'message' => "Success",
        ], 201);
    }

    public function update($employee_id, $id, EmployeeResidenceRequest $request, )
    {
        $residence = $this->service->update($id, $request->validated());

        return response()->json([
            'status'  => true,
            'data'    => [
                'residence' => $this->service->findById($id),
            ],
            'message' => "Success",
        ], 200);
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([], 204);
    }
}
