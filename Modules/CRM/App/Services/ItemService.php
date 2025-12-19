<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\ContactRepo;
use Modules\CRM\App\Repositories\ItemRepo;

class ItemService
{
    private $itemRepository;

    public function __construct(ItemRepo $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function paginate($request)
    {
        return $this->itemRepository->paginate($request);
    }

    public function get($id)
    {
        return $this->itemRepository->get($id);
    }

    public function create($data)
    {
        return $this->itemRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->itemRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->itemRepository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        $this->itemRepository->bulkDelete($ids);
    }
}
