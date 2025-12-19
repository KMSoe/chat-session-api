<?php
namespace Modules\Chat\App\Services;

interface ChatSessionInterface
{
    public function findAll($request);

    public function findById($id);

    public function findBySessionUuid($session_uuid);

    public function create(array $data);

    public function update($session_uuid, array $data);

    public function delete($session_uuid);

    public function bulkDelete(array $session_uuids);
}
