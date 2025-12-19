<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Http\Requests\ItemTemplateFormRequest;
use Modules\CRM\App\Http\Resources\ItemTemplateResource;
use Modules\CRM\App\Services\ItemTemplateService;

class ItemTemplateController extends Controller
{
    protected $service;

    public function __construct(ItemTemplateService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $templates = $this->service->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id' => Module::where('name', 'item_template')->first()?->id,
                'table_view_id' => TableView::fromName('item_template'),
                'templates'      => $templates,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemTemplateFormRequest $request)
    {
        $request->validated();
        try {
            $template = $this->service->create($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'template' => new ItemTemplateResource($template),
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
        $template = $this->service->get($id);
        
        return response()->json([
            'status' => true,
            'data'   => [
                'template' => new ItemTemplateResource($template),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemTemplateFormRequest $request, $id)
    {
        $request->validated();
        try {
            $template = $this->service->update($id, $request->all());
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'template' => new ItemTemplateResource($template),
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
            'ids.*' => 'exists:item_templates,id'
        ]);
        try {
            $this->service->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }
}
