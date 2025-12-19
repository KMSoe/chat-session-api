<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\ReceivedPaymentRepo;

class ReceivedPaymentService
{
    private $repository;

    public function __construct(ReceivedPaymentRepo $repository)
    {
        $this->repository = $repository;
    }

    public function paginate($request)
    {
        return $this->repository->paginate($request);
    }

    public function getContactsWithUnpaidInvoices($request)
    {
        return $this->repository->getContactsWithUnpaidInvoices($request);
    }

    public function get($id)
    {
        return $this->repository->get($id);
    }

    public function create($data)
    {
        return $this->repository->create($data);
    }

    public function recordPaymentForSpecificInvoice($data)
    {
        return $this->repository->recordPaymentForSpecificInvoice($data);
    }

    public function updatePaymentForSpecificInvoice($data)
    {
        return $this->repository->updatePaymentForSpecificInvoice($data);
    }

    public function refundPaymentForSpecificInvoice($data)
    {
        return $this->repository->refundPaymentForSpecificInvoice($data);
    }

    public function deletePaymentForSpecificInvoice($invoice_payment_id)
    {
        return $this->repository->deletePaymentForSpecificInvoice($invoice_payment_id);
    }

    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    public function refund($id, $data)
    {
        return $this->repository->refund($id, $data);
    }

    public function void($id, $data)
    {
        return $this->repository->void($id, $data);
    }

    public function delete($id)
    {
        $this->repository->delete($id);
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

    public function duplicate($id, $data)
    {
        return $this->repository->duplicate($id, $data);
    }

    public function getInitialData()
    {
        return $this->repository->getInitialData();
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
