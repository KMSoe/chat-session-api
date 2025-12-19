<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Http\Requests\TaxCalculationRequest;
use Modules\Payroll\App\resources\TaxCalculationResource;
use Modules\Payroll\App\Services\TaxCalculationService;

class TaxCalculationController extends Controller
{
    protected $service;

    public function __construct(TaxCalculationService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $tax_calculations = $this->service->findByParams($request->all());

        return response()->json([
            "status"  => true,
            "data"    => [
                'table_view_id' => TableView::TAX_CALCULATION->value,
                "tax_calculations" => $tax_calculations,
            ],
            "message" => "",
        ], 200);
    }

    public function show($id)
    {
        $tax_calculation = $this->service->findById($id);

        return response()->json([
            "status"  => true,
            "data"    => [
                "tax_calculation" => new TaxCalculationResource($tax_calculation),
            ],
            "message" => "",
        ], 200);
    }

    public function store(TaxCalculationRequest $request)
    {
        $tax_calculation = $this->service->store($request->validated());

        return response()->json([
            "status"  => true,
            "data"    => [
                "tax_calculation" => new TaxCalculationResource($this->service->findById($tax_calculation->id)),
            ],
            "message" => "Saved",
        ], 201);
    }

    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'in:Draft,Closed',
        ]);

        $tax_calculation = $this->service->updateStatus($id, $request->status);

        return response()->json([
            "status"  => true,
            "data"    => [
                "tax_calculation" => new TaxCalculationResource($this->service->findById($tax_calculation->id)),
            ],
            "message" => "Updated",
        ], 200);
    }

    public function recalculate($id)
    {
        $this->service->recalculate($id);

        return response()->json([
            "status"  => true,
            "data"    => [

            ],
            "message" => "recalculated",
        ], 200);
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([], 204);
    }
}
