<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\TaskRepo;

class TaskService
{
    private $taskRepository;

    public function __construct(TaskRepo $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function paginate($request)
    {
        return $this->taskRepository->paginate($request);
    }

    public function get($id)
    {
        return $this->taskRepository->get($id);
    }

    public function create($data)
    {
        return $this->taskRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->taskRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->taskRepository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        $this->taskRepository->bulkDelete($ids);
    }

    public function duplicate($id, $data)
    {
        return $this->taskRepository->duplicate($id, $data);
    }

    public function updateTaskPosition($taskId, $data)
    {
        return $this->taskRepository->updateTaskPosition($taskId, $data);
    }

    public function getActivityLogs($taskId)
    {
        return $this->taskRepository->getActivityLogs($taskId);
    }

    public function addComment($taskId, $data)
    {
        return $this->taskRepository->addComment($taskId, $data);
    }

    public function updateComment($taskId, $commentId, $data)
    {
        return $this->taskRepository->updateComment($taskId, $commentId, $data);
    }

    public function deleteComment($taskId, $commentId)
    {
        return $this->taskRepository->deleteComment($taskId, $commentId);
    }

    public function startEndTimer($taskId, $action)
    {
        return $this->taskRepository->startEndTimer($taskId, $action);
    }

    public function getTimeLogs($taskId)
    {
        return $this->taskRepository->getTimeLogs($taskId);
    }

    public function getMyAssignedTasks(array $data)
    {
        return $this->taskRepository->getMyAssignedTasks($data);
    }

    public function getTodayTasks(array $data)
    {
        return $this->taskRepository->getTodayTasks($data);
    }

    public function submitDailyReport(array $data)
    {
        return $this->taskRepository->submitDailyReport($data);
    }

    public function getTimerStatus($taskId)
    {
        return $this->taskRepository->getTimerStatus($taskId);
    }

    public function getDailyReports(array $data)
    {
        return $this->taskRepository->getDailyReports($data);
    }

    public function getActiveTimeReports(array $data)
    {
        return $this->taskRepository->getActiveTimeReports($data);
    }
}
