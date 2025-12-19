<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\CompanyRepo;

class CompanyService
{
    private $companyRepository;

    public function __construct(CompanyRepo $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function paginate($request)
    {
        return $this->companyRepository->paginate($request);
    }

    public function get($id)
    {
        return $this->companyRepository->get($id);
    }

    public function create($data)
    {
        return $this->companyRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->companyRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->companyRepository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        $this->companyRepository->bulkDelete($ids);
    }

    public function getComments($companyId)
    {
        return $this->companyRepository->getComments($companyId);
    }

    public function addComment($companyId, $data)
    {
        return $this->companyRepository->addComment($companyId, $data);
    }

    public function updateComment($companyId, $commentId, $data)
    {
        return $this->companyRepository->updateComment($companyId, $commentId, $data);
    }

    public function deleteComment($companyId, $commentId)
    {
        $this->companyRepository->deleteComment($companyId, $commentId);
    }
}