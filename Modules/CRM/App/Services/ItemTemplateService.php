<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\ItemTemplateRepo;

class ItemTemplateService
{
    private $itemTemplateRepository;

    public function __construct(ItemTemplateRepo $itemTemplateRepository)
    {
        $this->itemTemplateRepository = $itemTemplateRepository;
    }

    public function paginate($request)
    {
        return $this->itemTemplateRepository->paginate($request);
    }

    public function get($id)
    {
        return $this->itemTemplateRepository->get($id);
    }

    public function create($data)
    {
        return $this->itemTemplateRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->itemTemplateRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->itemTemplateRepository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        $this->itemTemplateRepository->bulkDelete($ids);
    }
}
