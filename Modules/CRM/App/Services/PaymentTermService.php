<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\PaymentTermRepo;

class PaymentTermService
{
    private $repo;

    public function __construct(PaymentTermRepo $repo)
    {
        $this->repo = $repo;
    }

    public function paginate($request)
    {
        return $this->repo->paginate($request);
    }

    public function get($id)
    {
        return $this->repo->get($id);
    }

    public function create($data)
    {
        return $this->repo->create($data);
    }

    public function update($id, $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete($id)
    {
        $this->repo->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        $this->repo->bulkDelete($ids);
    }
}
