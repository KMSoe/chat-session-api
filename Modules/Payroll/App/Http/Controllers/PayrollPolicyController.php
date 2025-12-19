<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Http\Requests\CreatePayrollPolicyRequest;
use Modules\Payroll\App\Http\Requests\UpdatePayrollPolicyRequest;
use Modules\Payroll\App\resources\PayrollPolicyResource;
use Modules\Payroll\App\Services\PayrollPolicyService;

class PayrollPolicyController extends Controller
{
    protected $service;

    public function __construct(PayrollPolicyService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $policies = $this->service->findByParams($request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
                'table_view_id'    => TableView::PAYROLL_POLICY->value,
                'payroll_policies' => $policies,
            ],
            'message' => '',
        ], 200);
    }

    public function show($id)
    {
        $policy = $this->service->findById($id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll_policy' => new PayrollPolicyResource($policy),
            ],
            'message' => '',
        ], 200);
    }

    public function store(CreatePayrollPolicyRequest $request)
    {
        $policy = $this->service->create($request->validated());
        event(new \App\Events\DatabaseForCacheUpdated(\App\Enums\CacheKeys::PAYROLL_POLICIES->name));

        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll_policy' => new PayrollPolicyResource($policy),
            ],
            'message' => 'Saved',
        ], 201);
    }

    public function update(UpdatePayrollPolicyRequest $request, $id)
    {
        $policy = $this->service->update($id, $request->validated());
        event(new \App\Events\DatabaseForCacheUpdated(\App\Enums\CacheKeys::PAYROLL_POLICIES->name));

        if (! $policy) {
            return response()->json(['message' => 'Payroll Policy not found'], 404);
        }

        return response()->json([
            'status'  => true,
            'data'    => [
                'payroll_policy' => new PayrollPolicyResource($this->service->findById($id)),
            ],
            'message' => 'Updated',
        ], 200);
    }

    public function destroy($id)
    {
        $deleted = $this->service->delete($id);
        event(new \App\Events\DatabaseForCacheUpdated(\App\Enums\CacheKeys::PAYROLL_POLICIES->name));

        if (! $deleted) {
            return response()->json(['message' => 'Payroll Policy not found'], 404);
        }

        return response()->json([], 204);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:payroll_policies,id',
        ]);

        $this->service->bulkDelete($request->ids);
        event(new \App\Events\DatabaseForCacheUpdated(\App\Enums\CacheKeys::PAYROLL_POLICIES->name));

        return response()->json([], 204);
    }
}
