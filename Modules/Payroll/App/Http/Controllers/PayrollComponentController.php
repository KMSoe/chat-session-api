<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Enums\ModuleNames;
use App\Enums\TableView;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Enums\PayrollComponentScopeModes;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Modules\Payroll\App\Http\Requests\StorePayrollComponentRequest;
use Modules\Payroll\App\Http\Requests\UpdatePayrollComponentRequest;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\resources\PayrollComponentResource;
use Modules\Payroll\App\Services\PayrollComponentService;

class PayrollComponentController extends Controller
{
    private $service;

    public function __construct(
        PayrollComponentService $service
    ) {
        $this->service = $service;

        // $this->authorizeResource(PayrollComponent::class, ModuleNames::PAYROLL_COMPONENT->value);
    }

    public function index(Request $request)
    {
        $components = $this->service->findByParams($request);

        return response()->json([
            'status'  => true,
            'data'    => [
                'table_view_id'      => TableView::PAYROLL_COMPONENT->value,
                'payroll_components' => $components,
            ],
            'message' => '',
        ], 200);
    }

    public function getPageData()
    {
        return response()->json([
            'status'  => true,
            'data'    => [
                'component_types'              => PayrollComponentTypes::values(),
                'component_scope_mode'         => PayrollComponentScopeModes::values(),
                'payroll_component_categories' => $this->service->getPayrollComponentCategories(),
                'system_built_in_components'   => $this->service->getSystemBuiltInComponents(),
            ],
            'message' => '',
        ], 200);
    }

    public function store(StorePayrollComponentRequest $request)
    {
        $component = $this->service->create($request->toArray());

        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll_components' => new PayrollComponentResource($this->service->findById($component->id)),
            ],
            'message' => 'Saved',
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll_component' => new PayrollComponentResource($this->service->findById($id)),
            ],
            'message' => '',
        ], 200);
    }

    public function update(UpdatePayrollComponentRequest $request, $id)
    {
        $this->service->update($id, $request->toArray());

        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll_component' => new PayrollComponentResource($this->service->findById($id)),
            ],
            'message' => 'Updated',
        ], 200);
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([], 204);
    }

    public function getByType(Request $request, $type)
    {
        $request->merge([
            'component_type' => $type,
        ]);

        $components = $this->service->findByParams($request);

        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll_component' => $components,
            ],
            'message' => '',
        ], 200);
    }
}
