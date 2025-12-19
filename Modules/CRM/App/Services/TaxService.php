<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\TaxRepo;

class TaxService
{
    private $taxRepository;

    public function __construct(TaxRepo $taxRepository)
    {
        $this->taxRepository = $taxRepository;
    }

    public function paginate($request)
    {
        return $this->taxRepository->paginate($request);
    }

    public function get($id)
    {
        return $this->taxRepository->get($id);
    }

    public function create($data)
    {
        return $this->taxRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->taxRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->taxRepository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        $this->taxRepository->bulkDelete($ids);
    }
}
