<?php
namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\Models\PayrollSlipTemplate;
use Modules\Payroll\App\Models\PayrollSlipTemplateDeductionComponent;
use Modules\Payroll\App\Models\PayrollSlipTemplateEarningComponent;
use Modules\Payroll\App\Services\PayrollSlipTemplateService;

class PayrollSlipTemplateSeeder extends Seeder
{
    protected $service;

    public function __construct(PayrollSlipTemplateService $service)
    {
        $this->service = $service;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $earningComponents   = PayrollComponent::where('is_system_default', true)->where('component_type', PayrollComponentTypes::EARNING->value)->get();
        $deductionComponents = PayrollComponent::where('is_system_default', true)->where('component_type', PayrollComponentTypes::DEDUCTION->value)->get();

        $earningComponentsData   = [];
        $deductionComponentsData = [];

        foreach ($earningComponents as $key => $component) {
            $earningComponentsData[] = [
                "payroll_component_id" => $component->id,
                "order"                => $key + 1,
            ];
        }

        foreach ($deductionComponents as $key => $component) {
            $deductionComponentsData[] = [
                "payroll_component_id" => $component->id,
                "label"                => $component->name,
            ];
        }

        $template_data = [
            'name'                               => 'Default Payslip',
            'description'                        => 'Standard payslip template',
            'is_default'                         => true,
            'remove_components_with_zero_amount' => false,
            'company_info_items'                 => [
                [
                    "label"      => "Company Name",
                    "access_key" => "display_name",
                ],
            ],
            'employee_info_items'                => [
                [
                    "label"      => "Employee Code",
                    "access_key" => "employee_code",
                ],
                [
                    "label"      => "Employee Name",
                    "access_key" => "name",
                ],
            ],
            'enable_leave_section'               => true,
        ];

        DB::beginTransaction();
        $template = PayrollSlipTemplate::create($template_data);

        foreach ($earningComponentsData as $ec) {
            PayrollSlipTemplateEarningComponent::create([
                'payroll_slip_template_id' => $template->id,
                'payroll_component_id'     => $ec['payroll_component_id'],
                'order'                    => $ec['order'] ?? 0,
            ]);
        }

        foreach ($deductionComponentsData as $dc) {
            PayrollSlipTemplateDeductionComponent::create([
                'payroll_slip_template_id' => $template->id,
                'payroll_component_id'     => $dc['payroll_component_id'],
                'order'                    => $dc['order'] ?? 0,
            ]);
        }

        DB::commit();

    }

}
