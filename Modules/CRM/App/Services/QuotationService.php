<?php
namespace Modules\CRM\App\Services;

use Modules\CRM\App\Repositories\QuotationRepo;

class QuotationService
{
    private $quotationRepository;

    public function __construct(QuotationRepo $quotationRepository)
    {
        $this->quotationRepository = $quotationRepository;
    }

    public function paginate($request)
    {
        return $this->quotationRepository->paginate($request);
    }

    public function getQuotationBoard($request)
    {
        return $this->quotationRepository->getQuotationBoard($request);
    }

    public function get($id)
    {
        return $this->quotationRepository->get($id);
    }

    public function create($data)
    {
        return $this->quotationRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->quotationRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->quotationRepository->delete($id);
    }

    public function approvalStatusUpdate(array $data)
    {
        return $this->quotationRepository->approvalStatusUpdate($data);
    }

    public function quotationStatusUpdate($id, array $data)
    {
        return $this->quotationRepository->quotationStatusUpdate($id, $data);
    }

    public function getActivityLogs($quotationId)
    {
        return $this->quotationRepository->getActivityLogs($quotationId);
    }

    public function getComments($quotationId)
    {
        return $this->quotationRepository->getComments($quotationId);
    }

    public function addComment($quotationId, $data)
    {
        return $this->quotationRepository->addComment($quotationId, $data);
    }

    public function updateComment($quotationId, $commentId, $data)
    {
        return $this->quotationRepository->updateComment($quotationId, $commentId, $data);
    }

    public function deleteComment($quotationId, $commentId)
    {
        return $this->quotationRepository->deleteComment($quotationId, $commentId);
    }

    public function sentMail($id, $data)
    {
        return $this->quotationRepository->sentMail($id, $data);
    }

    public function getInitialData()
    {
        return $this->quotationRepository->getInitialData();
    }

    public function getSharedLinks($id)
    {
        return $this->quotationRepository->getSharedLinks($id);
    }

    public function generateSharedLink($id, $data)
    {
        return $this->quotationRepository->generateSharedLink($id, $data);
    }

    public function disableSharedLink($id, $data)
    {
        return $this->quotationRepository->disableSharedLink($id, $data);
    }

    public function attachFile($id, $data)
    {
        return $this->quotationRepository->attachFile($id, $data);
    }

    public function deleteAttachedFile($id, $fileId)
    {
        return $this->quotationRepository->deleteAttachedFile($id, $fileId);
    }

    public function signQuotation($id, $data)
    {
        return $this->quotationRepository->signQuotation($id, $data);
    }

    public function signClientQuotation($id, $data)
    {
        return $this->quotationRepository->signClientQuotation($id, $data);
    }

    public function updateClientQuotation($id, $data)
    {
        return $this->quotationRepository->updateClientQuotation($id, $data);
    }

    public function getQuotationPublicDetails($id, $data)
    {
        return $this->quotationRepository->getQuotationPublicDetails($id, $data);
    }

    public function generatePdf($id)
    {
        return $this->quotationRepository->generatePdf($id);
    }
}
