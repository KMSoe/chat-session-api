<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Modules\Payroll\App\Http\Requests\CreatePayrollSlipTemplateRequest;
use Modules\Payroll\App\Http\Requests\UpdatePayrollSlipTemplateRequest;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\resources\PayrollSlipTemplateResource;
use Modules\Payroll\App\Services\PayrollSlipTemplateService;

class PayrollSlipTemplateController extends Controller
{
    protected $service;

    public function __construct(PayrollSlipTemplateService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $templates = $this->service->findByParams($request);

        return response()->json([
            'status'  => true,
            'data'    => [
                'table_view_id'          => TableView::PAYROLL_SLIP_TEMPLATE->value,
                'payroll_slip_templates' => $templates],
            'message' => '',
        ], 200);
    }

    public function getFormData()
    {

        return response()->json([
            'status'  => true,
            'data'    => [
                'company'              => new CompanyResource(Company::with(['logoFile'])->first()),
                'company_info_items'   => [
                    [
                        'label'      => 'Company Name',
                        'access_key' => 'display_name',
                    ],
                    [
                        'label'      => 'Phone Number',
                        'access_key' => 'phone_number',
                    ],
                    [
                        'label'      => 'Address',
                        'access_key' => 'address',
                    ],
                ],
                'employee_info_items'  => [
                    [
                        'label'      => 'Employee ID',
                        'access_key' => 'employee_code',
                    ],
                    [
                        'label'      => 'Group',
                        'access_key' => 'group',
                    ],
                    [
                        'label'      => 'Department',
                        'access_key' => 'department',
                    ],
                    [
                        'label'      => 'Designation',
                        'access_key' => 'designation',
                    ],
                    [
                        'label'      => 'Bank Name',
                        'access_key' => 'bank_name',
                    ],
                    [
                        'label'      => 'Bank Account Number',
                        'access_key' => 'bank_account_number',
                    ],
                ],
                'earning_components'   => PayrollComponent::where('component_type', PayrollComponentTypes::EARNING->value)
                    ->orWhere('component_type', PayrollComponentTypes::OTHERS->value)->get(),
                'deduction_components' => PayrollComponent::where('component_type', PayrollComponentTypes::DEDUCTION->value)
                    ->orWhere('component_type', PayrollComponentTypes::OTHERS->value)->get(),
            ],
            'message' => '',
        ], 200);
    }

    public function show($id)
    {
        $template = $this->service->get($id);

        return response()->json([
            'status'  => true,
            'data'    => ['payroll_slip_template' => new PayrollSlipTemplateResource($template)],
            'message' => '',
        ], 200);
    }

    public function store(CreatePayrollSlipTemplateRequest $request)
    {
        $template = $this->service->store($request->all());

        return response()->json([
            'status'  => true,
            'data'    => ['payroll_slip_template' => new PayrollSlipTemplateResource($this->service->get($template->id))],
            'message' => 'Payroll Slip Template created successfully',
        ], 201);
    }

    public function update(UpdatePayrollSlipTemplateRequest $request, $id)
    {
        $this->service->update($id, $request->all());

        return response()->json([
            'status'  => true,
            'data'    => ['payroll_slip_template' => new PayrollSlipTemplateResource($this->service->get($id))],
            'message' => 'Payroll Slip Template updated successfully',
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
            'ids.*' => 'exists:payroll_slip_templates,id',
        ]);

        $this->service->bulkDelete($request->ids);

        return response()->json([], 204);
    }
}
