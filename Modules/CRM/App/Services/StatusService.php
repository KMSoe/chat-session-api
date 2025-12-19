<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\StatusRepo;

class StatusService
{
    private $statusRepository;

    public function __construct(StatusRepo $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    public function getAll($projectId, $data)
    {
        return $this->statusRepository->getAll($projectId, $data);
    }

    public function create($projectId, $data)
    {
        return $this->statusRepository->create($projectId, $data);
    }

    public function update($projectId, $statusId, $data)
    {
        return $this->statusRepository->update($projectId, $statusId, $data);
    }

    public function bulkDelete($projectId, array $ids)
    {
        $this->statusRepository->bulkDelete($projectId, $ids);
    }

    public function getTaskStatuses($projectId)
    {
        return $this->statusRepository->getTaskStatuses($projectId);
    }

    public function createTaskStatus($projectId, $data)
    {
        return $this->statusRepository->createTaskStatus($projectId, $data);
    }

    public function updateTaskStatus($projectId, $taskId, $data)
    {
        return $this->statusRepository->updateTaskStatus($projectId, $taskId, $data);
    }

    public function bulkDeleteTaskStatuses($projectId, array $ids)
    {
        $this->statusRepository->bulkDeleteTaskStatuses($projectId, $ids);
    }

    public function getStatusSettings($data)
    {
        return $this->statusRepository->getStatusSettings($data);
    }

    public function createStatusSetting($data)
    {
        return $this->statusRepository->createStatusSetting($data);
    }

    public function updateStatusSetting($id, $data)
    {
        return $this->statusRepository->updateStatusSetting($id, $data);
    }

    public function bulkDeleteStatusSettings(array $ids)
    {
        $this->statusRepository->bulkDeleteStatusSettings($ids);
    }
}
