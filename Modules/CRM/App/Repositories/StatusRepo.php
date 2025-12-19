<?php

namespace Modules\CRM\App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\CRM\App\Http\Resources\StatusResource;
use Modules\CRM\App\Http\Resources\StatusSettingResource;
use Modules\CRM\App\Models\ProjectStatus;
use Modules\CRM\App\Models\ProjectTaskStatus;
use Modules\CRM\App\Models\StatusSetting;

class StatusRepo
{
    /**
     * Get All Statuses.
     */
    public function getAll($projectId, $data)
    {
        $statuses = ProjectStatus::where('project_id', $projectId)->get();

        return StatusResource::collection($statuses);
    }

    /**
     * Get Statuses With Pagination.
     */
    public function paginate($projectId, $request)
    {
        $perPage = $request['per_page'] ?? 20;

        $statuses = ProjectStatus::query();

        $statuses->orderBy('created_at', 'DESC');

        $statuses = $statuses->where('project_id', $projectId)->paginate($perPage);

        $data = $statuses->getCollection()->map(function ($item) {
            return new StatusResource($item);
        });

        return $statuses->setCollection($data);
    }

    /**
     * Create Status
     */
    public function create($projectId, array $data)
    {
        $data['project_id'] = $projectId;
        $status = ProjectStatus::create($data);

        return $status;
    }

    /**
     * Update Status
     */
    public function update($projectId, $statusId, array $data)
    {
        $status = ProjectStatus::where('project_id', $projectId)->where('id', $statusId)->firstOrFail();

        $status->update($data);

        return $status;
    }
    

    /**
     * Bulk Delete
     */
    function bulkDelete($projectId, array $ids) 
    {
        $statuses = ProjectStatus::whereIn('id', $ids)->where('project_id', $projectId)->get();
        foreach ($statuses as $key => $status) {
            $status->delete();
        }
    }

    public function getTaskStatuses($projectId)
    {
        return ProjectTaskStatus::where('project_id', $projectId)->get();
    }

    public function createTaskStatus($projectId, array $data)
    {
        $data['project_id'] = $projectId;
        $data['system_created'] = false;
        $status = ProjectTaskStatus::create($data);

        return $status;
    }

    public function updateTaskStatus($projectId, $taskId, array $data)
    {
        $status = ProjectTaskStatus::where('project_id', $projectId)
            ->where('id', $taskId)
            ->firstOrFail();

        if($status->system_created == true) {
            throw new \Exception("System created status cannot be updated.");
        }

        $status->update($data);

        return $status;
    }

    public function bulkDeleteTaskStatuses($projectId, array $ids)
    {
        $statuses = ProjectTaskStatus::whereIn('id', $ids)
            ->where('project_id', $projectId)
            ->get();

        foreach ($statuses as $key => $status) {
            if($status->system_created === false) {
                 $status->delete();
            }
        }
    }

    public function getStatusSettings($data)
    {
        $statuses = StatusSetting::query();

        if (isset($data['type'])) {
            $statuses->where('type', $data['type']);
        }

        return $statuses->get();
    }

    public function createStatusSetting(array $data)
    {
        $statusSetting = StatusSetting::create($data);

        return $statusSetting;
    }

    public function updateStatusSetting($id, array $data)
    {
        $statusSetting = StatusSetting::findOrFail($id);

        if (isset($data['is_default']) && $data['is_default'] == true) {
            StatusSetting::where('is_default', true)->update(['is_default' => false]);
        }

        $statusSetting->update($data);

        return $statusSetting;
    }


    public function bulkDeleteStatusSettings(array $ids)
    {
        $statusSettings = StatusSetting::whereIn('id', $ids)->get();
        foreach ($statusSettings as $key => $statusSetting) {
            $statusSetting->delete();
        }
    }
}
