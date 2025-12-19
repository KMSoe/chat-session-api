<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\ContactRepo;
use Modules\CRM\App\Repositories\ItemTypeRepo;

class ItemTypeService
{
    private $itemTypeRepository;

    public function __construct(ItemTypeRepo $itemTypeRepository)
    {
        $this->itemTypeRepository = $itemTypeRepository;
    }

    public function paginate($request)
    {
        return $this->itemTypeRepository->paginate($request);
    }

    public function getAll()
    {
        return $this->itemTypeRepository->getAll();
    }

    public function get($id)
    {
        return $this->itemTypeRepository->get($id);
    }

    public function create($data)
    {
        return $this->itemTypeRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->itemTypeRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->itemTypeRepository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        $this->itemTypeRepository->bulkDelete($ids);
    }
}
