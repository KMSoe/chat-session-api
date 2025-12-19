<?php

namespace App\Http\Controllers;

use App\Enums\AttributeType;
use App\Enums\TableView;
use App\Http\Requests\AttributeFormRequest;
use App\Http\Resources\AttributeCollection;
use App\Http\Resources\AttributeResource;
use App\Models\Module;
use App\Repositories\AttributeRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AttributeController extends Controller
{
    protected $attributeRepo;

    public function __construct(AttributeRepo $attributeRepo)
    {
        $this->attributeRepo = $attributeRepo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $attributes = $this->attributeRepo->paginate($request->all());
        
        return response()->json([
            'status' => true,
            'data'   => [
                'table_view_id' => TableView::fromName('attribute'),
                'attributes'    => $attributes,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttributeFormRequest $request)
    {
        $request->validated();
        try {
            $attribute = $this->attributeRepo->create($request->all());
            return response()->json([
                'status' => true,
                'data'   => [
                    'attribute' => new AttributeResource($attribute),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_OK);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $attribute = $this->attributeRepo->get($id);
        return response()->json([
            'status' => true,
            'data'   => [
                'attribute' => new AttributeResource($attribute),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttributeFormRequest $request, $id)
    {
        $request->validated();
        try {
            $attribute = $this->attributeRepo->update($id, $request->all());
            return response()->json([
                'status' => true,
                'data'   => [
                    'attribute' => new AttributeResource($attribute),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->attributeRepo->delete($id);
            return response()->json([
                'status'  => true,
                'message' => "Successfully deleted",
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function attributeTypes()
    {
        return response()->json([
            'types' => collect(AttributeType::cases())->map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])->values(),
        ]);
    }

    public function getByModule(string $moduleName)
    {
        $module = Module::whereRaw('BINARY `name` = ?', [$moduleName])->first();

        if (!$module) {
            return response()->json([
                'status' => false,
                'data' => null,
            ]);
        }

        $attributes = $module->attributes()->where('status', 1)->get();

        $fields = $attributes->mapWithKeys(function ($attribute) {
            // $value = $attribute->customValues->pluck('target_value')->toArray() ?? null;
            
            return [
                $attribute->name => [
                    // 'value' => $value,
                    'type' => $attribute->type,
                    'is_required' => (bool) $attribute->is_required,
                    'options' => in_array($attribute->type, ['select', 'radio', 'checkbox']) ? ($attribute->options ?? []) : null,
                    'group' => $attribute->attributeSets->pluck('id','name')->toArray(),
                ]
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $fields,
        ]);
    }

    public function changeStatus(Request $request, $id)
    {
        try {
            $attribute = $this->attributeRepo->updateStatus($id);
            return response()->json([
                'status' => true,
                'attribute_status' => $attribute->status,
                'message' => "Attribute status updated successfully",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function bulkDelete(Request $request) {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:attributes,id'
        ]);
        try {
            $this->attributeRepo->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function getOriginalFile(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'file_id' => 'required|exists:files,id',
        ]);

        try {
            $file = $this->attributeRepo->getOriginalFile($request->all());
            return response()->json([
                'status' => true,
                'data' => [
                    'file' => $file,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getOriginalHTML(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'encrypted_value' => 'required',
        ]);

        try {
            $value = $this->attributeRepo->getOriginalHTML($request->all());
            return response()->json([
                'status' => true,
                'data' => [
                    'decrypted_value' => $value,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
