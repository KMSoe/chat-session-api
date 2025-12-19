<?php
namespace Modules\Chat\App\Services\Impl;

use Modules\Chat\App\Models\ChatSession;
use Modules\Chat\App\Repositories\ChatSessionRepository;
use Modules\Chat\App\Services\ChatSessionInterface;

class ChatSessionImpl implements ChatSessionInterface
{
    private ChatSessionRepository $repository;

    public function __construct(ChatSessionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findAll($request)
    {
        return $this->repository->findAll($request);
    }

    public function findById($id)
    {
        return $this->repository->findById($id);

    }

    public function findBySessionUuid($session_uuid)
    {
        return $this->repository->findBySessionUuid($session_uuid);
    }

    public function create(array $data): ChatSession
    {
        return $this->repository->create($data);
    }

    public function update($session_uuid, array $data): void
    {
        $this->repository->update($session_uuid, $data);
    }

    public function delete($session_uuid)
    {
        $this->repository->delete($session_uuid);
    }

    public function bulkDelete(array $session_uuids)
    {
        $this->repository->bulkDelete($session_uuids);
    }
}
