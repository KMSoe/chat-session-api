<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\CreditNoteRepo;

class CreditNoteService
{
    private $repository;

    public function __construct(CreditNoteRepo $repository)
    {
        $this->repository = $repository;
    }

    public function paginate($request)
    {
        return $this->repository->paginate($request);
    }

    public function get($id)
    {
        return $this->repository->get($id);
    }

    public function create($data)
    {
        return $this->repository->create($data);
    }

    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    public function applyToInvoices($id, array $data)
    {
        return $this->repository->applyToInvoices($id, $data);
    }

    public function applyForSpecificInvoice($data)
    {
        return $this->repository->applyForSpecificInvoice($data);
    }

    public function deleteCreditNoteApplication($credit_note_application_id)
    {
        return $this->repository->deleteCreditNoteApplication($credit_note_application_id);
    }

    public function updateRefundHistory($id, array $data)
    {
        return $this->repository->updateRefundHistory($id, $data);
    }

    public function deleteRefundHistory($id)
    {
        return $this->repository->deleteRefundHistory($id);
    }

    public function refund($id, array $data)
    {
        return $this->repository->refund($id, $data);
    }

    public function delete($id)
    {
        $this->repository->delete($id);
    }

    public function getInitialData()
    {
        return $this->repository->getInitialData();
    }

    public function getActivityLogs($creditNoteId)
    {
        return $this->repository->getActivityLogs($creditNoteId);
    }

    public function getComments($creditNoteId)
    {
        return $this->repository->getComments($creditNoteId);
    }

    public function addComment($creditNoteId, $data)
    {
        return $this->repository->addComment($creditNoteId, $data);
    }

    public function updateComment($creditNoteId, $commentId, $data)
    {
        return $this->repository->updateComment($creditNoteId, $commentId, $data);
    }

    public function deleteComment($creditNoteId, $commentId)
    {
        return $this->repository->deleteComment($creditNoteId, $commentId);
    }

    public function attachFile($id, $data)
    {
        return $this->repository->attachFile($id, $data);
    }

    public function deleteAttachedFile($id, $fileId)
    {
        return $this->repository->deleteAttachedFile($id, $fileId);
    }
}
