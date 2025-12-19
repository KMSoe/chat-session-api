<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Http\Requests\CreateMPFTrusteeRequest;
use Modules\Payroll\App\Http\Requests\UpdateMPFTrusteeRequest;
use Modules\Payroll\App\Services\MPFTrusteeService;

class MPFTrusteeController extends Controller
{
    protected $service;

    public function __construct(MPFTrusteeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $mpf_trustee = $this->service->findByParams($request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
                'mpf_trustee' => $mpf_trustee,
            ],
            'message' => '',
        ], 200);
    }

    public function show($id)
    {
        $trustee = $this->service->findById($id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'mpf_trustee' => $trustee,
            ],
            'message' => '',
        ]);
    }

    public function store(CreateMPFTrusteeRequest $request)
    {
        $trustee = $this->service->create($request->validated());

        return response()->json([
            'status'  => true,
            'data'    => [
                'mpf_trustee' => $trustee,
            ],
            'message' => 'Trustee created successfully',
        ], 201);
    }

    public function update(UpdateMPFTrusteeRequest $request, $id)
    {
        $trustee = $this->service->update($id, $request->validated());

        if (! $trustee) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        return response()->json([
            'status'  => true,
            'data'    => [
                'mpf_trustee' => $this->service->findById($id),

            ],
            'message' => 'Trustee updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $deleted = $this->service->delete($id);

        if (! $deleted) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        return response()->json([], 204);
    }
}
