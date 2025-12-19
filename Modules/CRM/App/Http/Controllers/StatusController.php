<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Http\Requests\ProjectStatusFormRequest;
use Modules\CRM\App\Http\Requests\StatusFormRequest;
use Modules\CRM\App\Http\Requests\StatusSettingFormRequest;
use Modules\CRM\App\Http\Requests\TaskStatusFormRequest;
use Modules\CRM\App\Http\Resources\StatusResource;
use Modules\CRM\App\Http\Resources\StatusSettingResource;
use Modules\CRM\App\Http\Resources\TaskStatusResource;
use Modules\CRM\App\Services\StatusService;

class StatusController extends Controller
{
    protected $service;

    public function __construct(StatusService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index($id, Request $request)
    {
        $statuses = $this->service->getAll($id, $request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'statuses' => $statuses,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($id, ProjectStatusFormRequest $request)
    {
        $request->validated();
        try {
            $status = $this->service->create($id, $request->all());
            return response()->json([
                'status' => true,
                'data'   => [
                    'status' => new StatusResource($status),
                ],
                'message' => 'Successfully saved'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update($id, ProjectStatusFormRequest $request, $statusId)
    {
        $request->validated();
        try {
            $project = $this->service->update($id, $statusId, $request->all());
            return response()->json([
                'status' => true,
                'data'   => [
                    'project' => new StatusResource($project),
                ],
                'message' => 'Successfully updated'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function bulkDelete($id, Request $request) 
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:project_statuses,id'
        ]);
        try {
            $this->service->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function getTaskStatuses($id, Request $request)
    {
        $statuses = $this->service->getTaskStatuses($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'statuses' => $statuses,
            ],
        ], 200);
    }

    public function storeTaskStatus($id, TaskStatusFormRequest $request)
    {
        $request->validated();
        try {
            $status = $this->service->createTaskStatus($id, $request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'status' => new TaskStatusResource($status),
                ],
                'message' => 'Successfully saved'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateTaskStatus($id, $taskId, TaskStatusFormRequest $request)
    {
        $request->validated();
        try {
            $project = $this->service->updateTaskStatus($id, $taskId, $request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'project' => new StatusResource($project),
                ],
                'message' => 'Successfully updated'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function bulkDeleteTaskStatuses($id, Request $request) 
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:project_task_statuses,id'
        ]);
        try {
            $this->service->bulkDeleteTaskStatuses($id, $request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function getStatusSettings(Request $request)
    {
        $statuses = $this->service->getStatusSettings($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'statuses' => StatusSettingResource::collection($statuses),
            ],
        ], 200);
    }

    public function storeStatusSetting(StatusSettingFormRequest $request)
    {
        $request->validated();
        try {
            $status = $this->service->createStatusSetting($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'status' => new StatusSettingResource($status),
                ],
                'message' => 'Successfully saved'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateStatusSetting($id, StatusSettingFormRequest $request)
    {
        $request->validated();
        try {
            $status = $this->service->updateStatusSetting($id, $request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'status' => new StatusSettingResource($status),
                ],
                'message' => 'Successfully updated'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function bulkDeleteStatusSettings(Request $request) 
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:status_settings,id'
        ]);
        try {
            $this->service->bulkDeleteStatusSettings($request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }
}
