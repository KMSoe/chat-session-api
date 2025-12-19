<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\resources\AverageDailyWageResource;
use Modules\Payroll\App\Services\AverageDailyWageService;

class AverageDailyWageController extends Controller
{
    protected $service;

    public function __construct(AverageDailyWageService $service)
    {
        $this->service = $service;
    }

    /**
     * Show a single AverageDailyWage record.
     */
    public function getFirst()
    {
        $adw = $this->service->findFirst();

        return response()->json([
            'status'  => true,
            'data'    => [
                'average_daily_wage' => new AverageDailyWageResource($adw),
            ],
            'message' => '',
        ], 200);
    }

    /**
     * Create or update AverageDailyWage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exclude_rest_days_from_adw'          => 'required|boolean',
            'exclude_holidays_from_adw'           => 'required|boolean',
            'minimum_employment_period_per_week'  => 'nullable|integer|min:0',
            'minimum_employment_period_per_month' => 'nullable|integer|min:0',
            'remarks'                             => 'nullable|string',
            'is_active'                           => 'nullable|boolean',
            'payroll_components'                  => 'array',
            'payroll_components.*'                => 'integer|exists:payroll_components,id',
            'exclude_leave_types'                 => 'array',
            'exclude_leave_types.*'               => 'integer|exists:leave_types,id',
            'applicable_to'                       => 'nullable|array',
            'applicable_to.*.scope'               => 'required|in:group,department,designation,employee',
            'applicable_to.*.ids'                 => 'required|array|min:1',
            'applicable_to.*.ids.*'               => 'integer|min:1',
        ]);

        $adw = $this->service->createOrUpdate($validated);

        return response()->json([
            'status'  => true,
            'data'    => [
                'average_daily_wage' => new AverageDailyWageResource($this->service->findFirst()),
            ],
            'message' => '',
        ], 200);
    }
}
