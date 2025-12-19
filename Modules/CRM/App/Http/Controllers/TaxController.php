<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use Modules\CRM\App\Services\TaxService;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Http\Requests\TaxRequest;
use Modules\CRM\App\Http\Resources\TaxResource;

class TaxController extends Controller
{
    protected $service;

    public function __construct(TaxService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $taxes = $this->service->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id' => Module::where('name', 'tax')->first()?->id,
                'table_view_id' => TableView::fromName('tax'),
                'taxes'      => $taxes,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaxRequest $request)
    {
        $request->validated();
        try {
            $tax = $this->service->create($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'tax' => new TaxResource($tax),
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
        $tax = $this->service->get($id);
        
        return response()->json([
            'status' => true,
            'data'   => [
                'tax' => new TaxResource($tax),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaxRequest $request, $id)
    {
        $request->validated();
        try {
            $tax = $this->service->update($id, $request->all());
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'tax' => new TaxResource($tax),
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
            'ids.*' => 'exists:taxes,id'
        ]);
        try {
            $this->service->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }
}
