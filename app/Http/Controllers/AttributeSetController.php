<?php

namespace App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Requests\AttributeSetFormRequest;
use App\Http\Resources\AttributeSetResource;
use App\Repositories\AttributeSetRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AttributeSetController extends Controller
{
    protected $repo;

    public function __construct(AttributeSetRepo $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $attribute_sets = $this->repo->paginate($request->all());
        
        return response()->json([
            'status' => true,
            'data'   => [
                'table_view_id' => TableView::fromName('attribute_set'),
                'attribute_sets' => $attribute_sets,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttributeSetFormRequest $request)
    {
        $request->validated();
        try {
            $attribute = $this->repo->create($request->all());
            return response()->json([
                'status' => true,
                'data'   => [
                    'attribute_set' => new AttributeSetResource($attribute),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false , 'message' => $th->getMessage()], Response::HTTP_OK);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $attribute = $this->repo->get($id);
        return response()->json([
            'status' => true,
            'data'   => [
                'attribute_set' => new AttributeSetResource($attribute),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttributeSetFormRequest $request, $id)
    {
        $request->validated();
        try {
            $attribute = $this->repo->update($id, $request->all());
            return response()->json([
                'status' => true,
                'data'   => [
                    'attribute_set' => new AttributeSetResource($attribute),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false , 'message' => $th->getMessage()], Response::HTTP_OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->repo->delete($id);
            return response()->json([
                'status'  => true,
                'message' => "Successfully deleted",
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function getByModule(String $module_name)
    {
        $attribute_sets = $this->repo->getByModule($module_name);

        return response()->json([
            'status' => true,
            'data'   => [
                'attribute_sets' => AttributeSetResource::collection($attribute_sets),
            ],
        ], 200);
    }
}
