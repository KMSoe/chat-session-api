<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\ProjectRepo;

class ProjectService
{
    private $projectRepository;

    public function __construct(ProjectRepo $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function paginate($request)
    {
        return $this->projectRepository->paginate($request);
    }

    public function get($id)
    {
        return $this->projectRepository->get($id);
    }

    public function create($data)
    {
        return $this->projectRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->projectRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->projectRepository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        $this->projectRepository->bulkDelete($ids);
    }

    public function duplicate($id, $data)
    {
        return $this->projectRepository->duplicate($id, $data);
    }

    public function updateStatusOrders($data)
    {
        return $this->projectRepository->updateStatusOrders($data);
    }

    public function getProjectBoard($projectId, $data)
    {
        return $this->projectRepository->getProjectBoard($projectId, $data);
    }
}
