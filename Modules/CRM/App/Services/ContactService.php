<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\ContactRepo;

class ContactService
{
    private $contactRepository;

    public function __construct(ContactRepo $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function paginate($request)
    {
        return $this->contactRepository->paginate($request);
    }

    public function get($id)
    {
        return $this->contactRepository->get($id);
    }

    public function create($data)
    {
        return $this->contactRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->contactRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->contactRepository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        $this->contactRepository->bulkDelete($ids);
    }

    public function updatePassword($id, $data)
    {
        return $this->contactRepository->updatePassword($id, $data);
    }
}
