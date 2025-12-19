<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Http\Requests\RecurringInvoiceRequest;
use Modules\CRM\App\Http\Resources\InvoiceListingResource;
use Modules\CRM\App\Http\Resources\RecurringInvoiceDetailsResource;
use Modules\CRM\App\Services\RecurringInvoiceService;

class RecurringInvoiceController extends Controller
{
    protected $service;

    public function __construct(RecurringInvoiceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recurringInvoices = $this->service->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id' => Module::where('name', 'recurring_invoice')->first()?->id,
                'table_view_id' => TableView::fromName('recurring_invoice'),
                'recurring_invoices'      => $recurringInvoices,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RecurringInvoiceRequest $request)
    {
        $request->validated();
        try {
            $recurringInvoice = $this->service->create($request->all());
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'recurring_invoice' => new RecurringInvoiceDetailsResource($recurringInvoice),
                ],
                'message' => 'Successfully saved'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $recurringInvoice = $this->service->get($id);
        
        return response()->json([
            'status' => true,
            'data'   => [
                'recurring_invoice' => new RecurringInvoiceDetailsResource($recurringInvoice),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RecurringInvoiceRequest $request, $id)
    {
        $request->validated();
        try {
            $recurringInvoice = $this->service->update($id, $request->all());
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'recurring_invoice' => new RecurringInvoiceDetailsResource($recurringInvoice),
                ],
                'message' => 'Successfully updated'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'status'  => true,
                'message' => "Successfully deleted",
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getActivityLogs($id)
    {
        try {
            $activityLogs = $this->service->getActivityLogs($id);

            return response()->json([
                'status' => true,
                'data'   => [
                    'activity_logs' => $activityLogs,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getGeneratedInvoices($id)
    {
        try {
            $recurringInvoice = $this->service->get($id);
            $generatedInvoices = $recurringInvoice->invoices->load('contact', 'company', 'actionOwners');

            return response()->json([
                'status' => true,
                'data'   => [
                    'generated_invoices' => InvoiceListingResource::collection($generatedInvoices),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
