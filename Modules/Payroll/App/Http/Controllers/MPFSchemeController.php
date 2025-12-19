<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Http\Requests\CreateMPFSchemeRequest;
use Modules\Payroll\App\Http\Requests\UpdateMPFSchemeRequest;
use Modules\Payroll\App\resources\MPFSchemeResource;
use Modules\Payroll\App\Services\MPFSchemeService;

class MPFSchemeController extends Controller
{
    protected $service;

    public function __construct(MPFSchemeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schemes = $this->service->findByParams($request->all());

        return response()->json([
            'status'  => true,
            'data'    => ['mpf_schemes' => $schemes],
            'message' => '',
        ], 200);
    }

    public function show($id)
    {
        $scheme = $this->service->get($id);

        return response()->json([
            'status'  => true,
            'data'    => ['mpf_scheme' => new MPFSchemeResource($scheme)],
            'message' => '',
        ], 200);
    }

    public function store(CreateMPFSchemeRequest $request)
    {
        $scheme = $this->service->create($request->validated());

        return response()->json([
            'status'  => true,
            'data'    => ['mpf_scheme' => new MpfSchemeResource($scheme)],
            'message' => 'Scheme created successfully',
        ], 201);
    }

    public function update(UpdateMPFSchemeRequest $request, $id)
    {
        $scheme = $this->service->update($id, $request->validated());

        return response()->json([
            'status'  => true,
            'data'    => ['mpf_scheme' => new MpfSchemeResource($scheme)],
            'message' => 'Scheme updated successfully',
        ], 200);
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([], 204);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:mpf_schemes,id',
        ]);

        $this->service->bulkDelete($request->ids);

        return response()->json([], 204);
    }
}
