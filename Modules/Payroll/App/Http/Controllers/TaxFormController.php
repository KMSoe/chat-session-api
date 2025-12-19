<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\App\Http\Requests\UpdateTaxFormIncomeCategoryComponentsRequest;
use Modules\Payroll\App\Services\TaxFormService;

class TaxFormController extends Controller
{
    protected TaxFormService $taxFormService;

    public function __construct(TaxFormService $taxFormService)
    {
        $this->taxFormService = $taxFormService;
    }

    /**
     * Display a listing of the tax forms.
     */
    public function index(Request $request)
    {
        $tax_forms = $this->taxFormService->findAll($request);

        return response()->json([
            'status'  => true,
            'data'    => [
                'tax_forms' => $tax_forms,
            ],
            'message' => 'Tax forms retrieved successfully',
        ], 200);

    }

    /**
     * Display the specified tax form.
     */
    public function show(int $id)
    {
        $tax_form = $this->taxFormService->findById($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'tax_form' => $tax_form,
            ],
            'message' => 'Tax form retrieved successfully',
        ], 200);

    }

    /**
     * Update the status of the specified tax form.
     */
    public function updateStatus(int $id)
    {
        $tax_form = $this->taxFormService->updateTaxFormStatus($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'tax_form' => $tax_form,
            ],
            'message' => "Success",
        ], 200);
    }

    public function getIncomeCategoryById(int $form_id, $income_category_id)
    {
        $income_category = $this->taxFormService->getIncomeCategoryById($income_category_id);

        return response()->json([
            'success' => true,
            'data'    => [
                'income_category' => $income_category,
            ],
            'message' => '',
        ], 200);
    }

    public function updateCategoryComponents($form_id, $income_category_id, UpdateTaxFormIncomeCategoryComponentsRequest $request)
    {
        $validated = $request->validated();

        $this->taxFormService->updateCategoryComponents(
            $validated['tax_form_income_category_id'],
            $validated['income_additions'] ?? null,
            $validated['income_deductions'] ?? null
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'income_category' => $this->taxFormService->getIncomeCategoryById($income_category_id),
            ],
            'message' => "Success",
        ]);
    }
}
