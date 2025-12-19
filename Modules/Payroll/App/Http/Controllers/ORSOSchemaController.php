<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Http\Requests\CreateORSOSchemeRequest;
use Modules\Payroll\App\Http\Requests\UpdateORSOSchemeRequest;
use Modules\Payroll\App\resources\ORSOSchemeResource;
use Modules\Payroll\App\Services\ORSOSchemeService;

class ORSOSchemaController extends Controller
{
    protected $service;

    public function __construct(ORSOSchemeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schemes = $this->service->findByParams($request->all());

        return response()->json([
            'status'  => true,
            'data'    => ['orso_schemes' => $schemes],
            'message' => '',
        ], 200);
    }

    public function show($id)
    {
        $scheme = $this->service->get($id);

        return response()->json([
            'status'  => true,
            'data'    => ['orso_scheme' => new ORSOSchemeResource($scheme)],
            'message' => '',
        ], 200);
    }

    public function store(CreateORSOSchemeRequest $request)
    {
        $scheme = $this->service->create($request->validated());

        return response()->json([
            'status'  => true,
            'data'    => ['orso_scheme' => new ORSOSchemeResource($scheme)],
            'message' => 'Scheme created successfully',
        ], 201);
    }

    public function update(UpdateORSOSchemeRequest $request, $id)
    {
        $scheme = $this->service->update($id, $request->validated());

        return response()->json([
            'status'  => true,
            'data'    => ['orso_scheme' => new ORSOSchemeResource($scheme)],
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
            'ids.*' => 'exists:orso_schemes,id',
        ]);

        $this->service->bulkDelete($request->ids);

        return response()->json([], 204);
    }
}
