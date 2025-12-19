<?php
namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Http\Requests\ItemRequest;
use Modules\CRM\App\resources\ItemResource;
use Modules\CRM\App\Services\ItemService;
use Modules\CRM\App\Services\ItemTypeService;

class ItemController extends Controller
{
    protected $service;
    protected $itemTypeService;

    public function __construct(ItemService $service, ItemTypeService $itemTypeService)
    {
        $this->service         = $service;
        $this->itemTypeService = $itemTypeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());

        // if ($request->export) {
        //     $format = strtolower($request->format) ?? 'excel';
        //     switch ($format) {
        //         case 'excel':
        //             return Excel::download(new itemExport($items), 'items.xlsx');
        //             break;
        //         case 'csv':
        //             return Excel::download(new itemExport($items), 'items.csv');
        //             break;
        //         default:
        //             return Excel::download(new itemExport($items), 'items.xlsx');
        //             break;
        //     }
        // }

        return response()->json([
            'status' => true,
            'data'   => [
                'table_view_id' => TableView::fromName('item'),
                'items'         => $items,
            ],
        ], 200);
    }

    public function getPageData()
    {
        return response()->json([
            'status' => true,
            'data'   => [
                'item_types' => $this->itemTypeService->getAll(),
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemRequest $request)
    {
        $request->validated();
        try {
            $item = $this->service->create($request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'item' => new ItemResource($item),
                ],
                'message' => 'Successfully saved',
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
        $item = $this->service->get($id);
        return response()->json([
            'status' => true,
            'data'   => [
                'item' => new ItemResource($item),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemRequest $request, $id)
    {
        $request->validated();
        try {
            $this->service->update($id, $request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
      
                ],
                'message' => 'Successfully updated',
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
            'ids'   => 'required|array',
            'ids.*' => 'exists:items,id',
        ]);
        try {
            $this->service->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function import(Request $request)
    {
        // $request->validate([
        //     'file' => 'required|mimes:xlsx,csv',
        // ], [
        //     'file' => "The file is required with excel(xlsx) or csv format",
        // ]);

        $import = new itemImport($this->service);
        Excel::import($import, $request->file('file'));

        $failures = $import->failures();

        if ($failures->isNotEmpty()) {
            $field_messages = [];

            foreach ($failures as $failure) {
                $row       = $failure->row();
                $attribute = $failure->attribute();
                $messages  = $failure->errors();
                $value     = $failure->values()[$attribute] ?? '[unknown]';

                foreach ($messages as $msg) {
                    $key = $msg;
                    if (! isset($field_messages[$attribute][$key])) {
                        $field_messages[$attribute][$key] = [];
                    }
                    $field_messages[$attribute][$key][] = "$value of row $row";
                }
            }

            $error_messages = [];

            foreach ($field_messages as $attribute => $message_group) {
                foreach ($message_group as $base_message => $entries) {
                    $entries = array_unique($entries);

                    if (count($entries) > 1) {
                        $last   = array_pop($entries);
                        $joined = implode(', ', $entries) . ' and ' . $last;
                    } else {
                        $joined = $entries[0];
                    }

                    $error_messages[$attribute][] = "[$joined] — $base_message";
                }
            }

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $error_messages,
            ], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => "Successfully imported",
        ], 200);
    }

    public function downloadSampleExcelFile()
    {
        $file = public_path('sample_import_data/item_import_sample.xlsx');

        return response()->download($file);
    }
}
