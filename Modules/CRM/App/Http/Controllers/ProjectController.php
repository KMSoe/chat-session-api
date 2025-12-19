<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\App\Exports\ProjectExport;
use Modules\CRM\App\Http\Requests\ProjectFormRequest;
use Modules\CRM\App\Http\Requests\ProjectTaskStatusOrderRequest;
use Modules\CRM\App\Http\Resources\ProjectBoardResource;
use Modules\CRM\App\Http\Resources\ProjectResource;
use Modules\CRM\App\Imports\ProjectImport;
use Modules\CRM\App\Models\Project;
use Modules\CRM\App\Services\ProjectService;

class ProjectController extends Controller
{
    protected $service;

    public function __construct(ProjectService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = $this->service->paginate($request->all());

        if ($request->export) {
            $format = strtolower($request->format) ?? 'excel';
            switch ($format) {
                case 'excel':
                    return Excel::download(new ProjectExport($projects), 'projects.xlsx');
                    break;
                case 'csv':
                    return Excel::download(new ProjectExport($projects), 'projects.csv');
                    break;
                default:
                    return Excel::download(new ProjectExport($projects), 'projects.xlsx');
                    break;
            }
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id' => Module::where('name', 'project')->first()?->id,
                'table_view_id' => TableView::fromName('project'),
                'projects'      => $projects,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectFormRequest $request)
    {
        $request->validated();
        try {
            $project = $this->service->create($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'project' => new ProjectResource($project),
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
        $project = $this->service->get($id);
        return response()->json([
            'status' => true,
            'data'   => [
                'project' => new ProjectResource($project),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectFormRequest $request, $id)
    {
        $request->validated();
        try {
            $project = $this->service->update($id, $request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'project' => new ProjectResource($project),
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

    public function bulkDelete(Request $request) {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:projects,id'
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

        $import = new ProjectImport($this->service);
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
        $file = public_path('sample_import_data/project_import_sample.xlsx');

        return response()->download($file);
    }

    public function duplicateProject($id, Request $request)
    {
        try {
            $newProject = $this->service->duplicate($id, $request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'project' => new ProjectResource($newProject),
                ],
                'message' => 'Successfully duplicated'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateStatusOrders(ProjectTaskStatusOrderRequest $request)
    {
        $request->validated();
        try {
            $this->service->updateStatusOrders($request->all());

            return response()->json([
                'status'  => true,
                'message' => "Successfully updated status orders",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getProjectBoard($id, Request $request)
    {
        try {
            $boardData = $this->service->getProjectBoard($id, $request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'board_data' => ProjectBoardResource::collection($boardData),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
