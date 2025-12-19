<?php
namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\App\Exports\TaskExport;
use Modules\CRM\App\Http\Requests\DailyReportFormRequest;
use Modules\CRM\App\Http\Requests\TaskFormRequest;
use Modules\CRM\App\Http\Resources\TaskCommentResource;
use Modules\CRM\App\Http\Resources\TaskResource;
use Modules\CRM\App\Http\Resources\TaskTimeLogResource;
use Modules\CRM\App\Imports\TaskImport;
use Modules\CRM\App\Services\TaskService;

class TaskController extends Controller
{
    protected $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tasks = $this->service->paginate($request->all());

        if ($request->export) {
            $format = strtolower($request->format) ?? 'excel';
            switch ($format) {
                case 'excel':
                    return Excel::download(new TaskExport($tasks), 'tasks.xlsx');
                    break;
                case 'csv':
                    return Excel::download(new TaskExport($tasks), 'tasks.csv');
                    break;
                default:
                    return Excel::download(new TaskExport($tasks), 'tasks.xlsx');
                    break;
            }
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id'     => Module::where('name', 'task')->first()?->id,
                'table_view_id' => TableView::fromName('task'),
                'tasks'         => $tasks,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskFormRequest $request)
    {
        $request->validated();
        try {
            $task = $this->service->create($request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'task' => new TaskResource($task),
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
        $task = $this->service->get($id);
        return response()->json([
            'status' => true,
            'data'   => [
                'task' => new TaskResource($task),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskFormRequest $request, $id)
    {
        $request->validated();
        try {
            $task = $this->service->update($id, $request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'task' => new TaskResource($task),
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
            'ids.*' => 'exists:tasks,id',
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

        $import = new TaskImport($this->service);
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
        $file = public_path('sample_import_data/task_import_sample.xlsx');

        return response()->download($file);
    }

    public function duplicateTask($id, Request $request)
    {
        try {
            $newTask = $this->service->duplicate($id, $request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'task' => new TaskResource($newTask),
                ],
                'message' => 'Successfully duplicated',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateTaskPosition($id, Request $request)
    {
        $data = $request->validate([
            'status_id'  => 'sometimes|required|exists:project_task_statuses,id',
            'sort_order' => 'sometimes|required|integer|min:1',
        ]);

        try {
            $task = $this->service->updateTaskPosition($id, $data);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'task' => new TaskResource($task),
                ],
                'message' => 'Task position updated successfully',
            ], 200);
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

    public function addComment($id, Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        try {
            $comment = $this->service->addComment($id, $data);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'comment' => new TaskCommentResource($comment),
                ],
                'message' => 'Comment added successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateComment($taskId, $commentId, Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        try {
            $comment = $this->service->updateComment($taskId, $commentId, $data);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'comment' => new TaskCommentResource($comment),
                ],
                'message' => 'Comment updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteComment($taskId, $commentId)
    {
        try {
            $this->service->deleteComment($taskId, $commentId);

            return response()->json([
                'status'  => true,
                'message' => 'Comment deleted successfully',
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function startEndTimer($id, Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:start,stop',
        ]);

        try {
            $timeLog = $this->service->startEndTimer($id, $data['action']);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'time_log' => new TaskTimeLogResource($timeLog),
                ],
                'message' => 'Timer ' . ($data['action'] === 'start' ? 'started' : 'stopped') . ' successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getTimeLogs($id)
    {
        try {
            $timeLogs = $this->service->getTimeLogs($id);

            return response()->json([
                'status' => true,
                'data'   => [
                    'time_logs' => TaskTimeLogResource::collection($timeLogs),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getMyAssignedTasks(Request $request)
    {
        $tasks = $this->service->getMyAssignedTasks($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'tasks' => $tasks,
            ],
        ], 200);
    }

    public function getTodayTasks(Request $request)
    {
        $tasks = $this->service->getTodayTasks($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'tasks' => $tasks,
            ],
        ], 200);
    }

    public function submitDailyReport(DailyReportFormRequest $request)
    {
        $data = $request->validated();

        try {
            $this->service->submitDailyReport($data);

            return response()->json([
                'status'  => true,
                'message' => 'Daily report submitted successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getTimerStatus($id)
    {
        try {
            $status = $this->service->getTimerStatus($id);

            return response()->json([
                'status' => true,
                'data'   => [
                    'timer_status' => $status,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getDailyReports(Request $request)
    {
        try {
            $reports = $this->service->getDailyReports($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'daily_reports' => $reports,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getActiveTimeReports(Request $request)
    {
        try {
            $reports = $this->service->getActiveTimeReports($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'active_time_reports' => $reports,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
