<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Http\Requests\PaymentTermFormRequest;
use Modules\CRM\App\Http\Resources\PaymentTermResource;
use Modules\CRM\App\Services\PaymentTermService;

class PaymentTermController extends Controller
{
    protected $service;

    public function __construct(PaymentTermService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $payment_terms = $this->service->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id' => Module::where('name', 'payment_term')->first()?->id,
                'table_view_id' => TableView::fromName('payment_term'),
                'payment_terms'      => $payment_terms,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentTermFormRequest $request)
    {
        $request->validated();
        try {
            $payment_term = $this->service->create($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'payment_term' => new PaymentTermResource($payment_term),
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
        $payment_term = $this->service->get($id);
        
        return response()->json([
            'status' => true,
            'data'   => [
                'payment_term' => new PaymentTermResource($payment_term),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentTermFormRequest $request, $id)
    {
        $request->validated();
        try {
            $payment_term = $this->service->update($id, $request->all());
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'payment_term' => new PaymentTermResource($payment_term),
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

    public function bulkDelete(Request $request) 
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:crm_payment_terms,id'
        ]);
        try {
            $this->service->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }
}
