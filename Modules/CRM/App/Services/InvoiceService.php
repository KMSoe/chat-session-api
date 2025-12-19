<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\InvoiceRepo;

class InvoiceService
{
    private $repository;

    public function __construct(InvoiceRepo $repository)
    {
        $this->repository = $repository;
    }

    public function paginate($request)
    {
        return $this->repository->paginate($request);
    }

    public function getInvoiceBoard($request)
    {
        return $this->repository->getInvoiceBoard($request);
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

    public function delete($id)
    {
        $this->repository->delete($id);
    }

    public function approvalStatusUpdate(array $data)
    {
        return $this->repository->approvalStatusUpdate($data);
    }

    public function getActivityLogs($invoiceId)
    {
        return $this->repository->getActivityLogs($invoiceId);
    }

    public function getComments($invoiceId)
    {
        return $this->repository->getComments($invoiceId);
    }

    public function addComment($invoiceId, $data)
    {
        return $this->repository->addComment($invoiceId, $data);
    }

    public function updateComment($invoiceId, $commentId, $data)
    {
        return $this->repository->updateComment($invoiceId, $commentId, $data);
    }

    public function deleteComment($invoiceId, $commentId)
    {
        return $this->repository->deleteComment($invoiceId, $commentId);
    }

    public function sentMail($id, $data)
    {
        return $this->repository->sentMail($id, $data);
    }

    public function getInitialData()
    {
        return $this->repository->getInitialData();
    }

    public function getSharedLinks($id)
    {
        return $this->repository->getSharedLinks($id);
    }

    public function generateSharedLink($id, $data)
    {
        return $this->repository->generateSharedLink($id, $data);
    }

    public function disableSharedLink($id, $data)
    {
        return $this->repository->disableSharedLink($id, $data);
    }

    public function attachFile($id, $data)
    {
        return $this->repository->attachFile($id, $data);
    }

    public function deleteAttachedFile($id, $fileId)
    {
        return $this->repository->deleteAttachedFile($id, $fileId);
    }

    public function signInvoice($id, $data)
    {
        return $this->repository->signInvoice($id, $data);
    }

    public function signClientInvoice($id, $data)
    {
        return $this->repository->signClientInvoice($id, $data);
    }

    public function getInvoicePublicDetails($id, $data)
    {
        return $this->repository->getInvoicePublicDetails($id, $data);
    }

    public function addExpectedPaymentDate($id, $data)
    {
        return $this->repository->addExpectedPaymentDate($id, $data);
    }

    public function markAsVoid($id, $data)
    {
        return $this->repository->markAsVoid($id, $data);
    }

    public function markAsWriteOff($id, $data)
    {
        return $this->repository->markAsWriteOff($id, $data);
    }

    public function cancelWriteOff($id, $data)
    {
        return $this->repository->cancelWriteOff($id, $data);
    }

    public function generatePdf($id)
    {
        return $this->repository->generatePdf($id);
    }
}
