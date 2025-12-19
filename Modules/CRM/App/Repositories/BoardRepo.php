<?php
namespace Modules\CRM\App\Repositories;

use App\Http\Services\ApproverService;
use Modules\CRM\App\Models\Invoice;
use Modules\CRM\App\Models\Quotation;

class BoardRepo
{
    protected $quotationRepository;
    protected $invoiceRepository;
    protected $approvalFlowService;

    public function __construct(QuotationRepo $quotationRepository, InvoiceRepo $invoiceRepository, ApproverService $approvalFlowService)
    {
        $this->approvalFlowService = $approvalFlowService;
        $this->quotationRepository = $quotationRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function boardStatusChange(array $data)
    {
        $id = $data['id'];
        $module = $data['module'];
        $fromStatus = $data['from_status'];
        $toStatus = $data['to_status'];

        if ($module === 'quotation') {
            return $this->handleQuotationStatusChange($id, $fromStatus, $toStatus);
        } elseif ($module === 'invoice') {
            return $this->handleInvoiceStatusChange($id, $fromStatus, $toStatus);
        }

        throw new \Exception('Invalid module type');
    }

    private function handleQuotationStatusChange($id, $fromStatus, $toStatus)
    {
        $quotation = Quotation::findOrFail($id);

        if ($quotation->quotation_status !== $fromStatus) {
            throw new \Exception('Invalid status transition. Current status is ' . $quotation->quotation_status);
        }

        switch ($fromStatus . '_to_' . $toStatus) {
            // From DRAFT transitions
            case 'draft_to_pending_approval':
                $this->approvalFlowService->createActionOwner('quotation', $quotation->id);
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation sent for approval']);
                break;

            case 'draft_to_sent':
                $this->quotationRepository->sentMail($quotation, $data = []);
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation sent to customer']);
                break;

            case 'draft_to_approved':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation approved']);
                break;

            // From PENDING_APPROVAL transitions
            case 'pending_approval_to_approved':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation approved']);
                break;

            case 'pending_approval_to_rejected':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation rejected']);
                break;

            case 'pending_approval_to_draft':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation moved back to draft']);
                break;

            // From APPROVED transitions
            case 'approved_to_sent':
                $this->quotationRepository->sentMail($quotation, $data = []);
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Approved quotation sent to customer']);
                break;

            case 'approved_to_draft':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation moved back to draft']);
                break;

            // From REJECTED transitions
            case 'rejected_to_draft':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Rejected quotation moved back to draft']);
                break;

            case 'rejected_to_pending_approval':
                $this->approvalFlowService->createActionOwner('quotation', $quotation->id);
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Rejected quotation resubmitted for approval']);
                break;

            // From SENT transitions
            case 'sent_to_accepted':
                $quotation->accepted_at = now();
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation accepted by customer']);
                break;

            case 'sent_to_declined':
                $quotation->declined_at = now();
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation declined by customer']);
                break;

            case 'sent_to_expired':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation marked as expired']);
                break;

            // From ACCEPTED transitions
            case 'accepted_to_invoiced':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation converted to invoice']);
                break;

            case 'accepted_to_sent':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Quotation moved back to sent status']);
                break;

            // From DECLINED transitions
            case 'declined_to_sent':
                $quotation->declined_at = null;
                $quotation->decline_reason = null;
                $this->quotationRepository->sentMail($quotation, $data = []);
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Declined quotation resent to customer']);
                break;

            case 'declined_to_draft':
                $quotation->declined_at = null;
                $quotation->decline_reason = null;
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Declined quotation moved to draft']);
                break;

            // From EXPIRED transitions
            case 'expired_to_sent':
                $this->quotationRepository->sentMail($quotation, $data = []);
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Expired quotation resent to customer']);
                break;

            case 'expired_to_draft':
                $this->quotationRepository->createActivityLog($quotation, ['description' => 'Expired quotation moved to draft']);
                break;

            default:
                $this->quotationRepository->createActivityLog($quotation, [
                    'description' => "Quotation status changed from {$fromStatus} to {$toStatus}"
                ]);
                break;
        }

        $quotation->update(['quotation_status' => $toStatus]);

        return $quotation;
    }

    private function handleInvoiceStatusChange($id, $fromStatus, $toStatus)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->invoice_status !== $fromStatus) {
            throw new \Exception('Invalid status transition. Current status is ' . $invoice->invoice_status);
        }

        switch ($fromStatus . '_to_' . $toStatus) {
            // From DRAFT transitions
            case 'draft_to_pending_approval':
                $this->approvalFlowService->createActionOwner('invoice', $invoice->id);
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice sent for approval']);
                break;

            case 'draft_to_sent':
                $this->invoiceRepository->sentMail($invoice, $data = []);
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice sent to customer']);
                break;

            case 'draft_to_approved':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice approved']);
                break;

            // From PENDING_APPROVAL transitions
            case 'pending_approval_to_approved':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice approved']);
                break;

            case 'pending_approval_to_rejected':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice rejected']);
                break;

            case 'pending_approval_to_draft':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice moved back to draft']);
                break;

            // From APPROVED transitions
            case 'approved_to_sent':
                $this->invoiceRepository->sentMail($invoice, $data = []);
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Approved invoice sent to customer']);
                break;

            case 'approved_to_unpaid':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice marked as unpaid']);
                break;

            case 'approved_to_draft':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice moved back to draft']);
                break;

            // From REJECTED transitions
            case 'rejected_to_draft':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Rejected invoice moved back to draft']);
                break;

            case 'rejected_to_pending_approval':
                $this->approvalFlowService->createActionOwner('invoice', $invoice->id);
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Rejected invoice resubmitted for approval']);
                break;

            // From SENT transitions
            case 'sent_to_unpaid':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice marked as unpaid']);
                break;

            case 'sent_to_paid':
                $invoice->last_payment_date = now();
                $invoice->balance_due = 0;
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice marked as paid']);
                break;

            case 'sent_to_overdue':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice marked as overdue']);
                break;

            case 'sent_to_void':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice voided']);
                break;

            // From UNPAID transitions
            case 'unpaid_to_partially_paid':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice partially paid']);
                break;

            case 'unpaid_to_paid':
                $invoice->last_payment_date = now();
                $invoice->balance_due = 0;
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice fully paid']);
                break;

            case 'unpaid_to_overdue':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice marked as overdue']);
                break;

            case 'unpaid_to_void':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice voided']);
                break;

            case 'unpaid_to_write_off':
                $invoice->balance_due = 0;
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice written off']);
                break;

            // From PARTIALLY_PAID transitions
            case 'partially_paid_to_paid':
                $invoice->last_payment_date = now();
                $invoice->balance_due = 0;
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Invoice fully paid']);
                break;

            case 'partially_paid_to_overdue':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Partially paid invoice marked as overdue']);
                break;

            case 'partially_paid_to_void':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Partially paid invoice voided']);
                break;

            case 'partially_paid_to_write_off':
                $invoice->balance_due = 0;
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Partially paid invoice written off']);
                break;

            // From OVERDUE transitions
            case 'overdue_to_paid':
                $invoice->last_payment_date = now();
                $invoice->balance_due = 0;
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Overdue invoice paid']);
                break;

            case 'overdue_to_partially_paid':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Overdue invoice partially paid']);
                break;

            case 'overdue_to_void':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Overdue invoice voided']);
                break;

            case 'overdue_to_write_off':
                $invoice->balance_due = 0;
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Overdue invoice written off']);
                break;

            // From PAID transitions
            case 'paid_to_void':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Paid invoice voided']);
                break;

            case 'paid_to_partially_paid':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Paid invoice moved to partially paid (refund/adjustment)']);
                break;

            // From VOID transitions
            case 'void_to_draft':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Voided invoice moved to draft']);
                break;

            case 'void_to_unpaid':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Voided invoice reactivated as unpaid']);
                break;

            // From WRITE_OFF transitions
            case 'write_off_to_unpaid':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Written off invoice reactivated as unpaid']);
                break;

            case 'write_off_to_draft':
                $this->invoiceRepository->createActivityLog($invoice, ['description' => 'Written off invoice moved to draft']);
                break;

            default:
                // Allow any other transitions without specific logic
                $this->invoiceRepository->createActivityLog($invoice, [
                    'description' => "Invoice status changed from {$fromStatus} to {$toStatus}"
                ]);
                break;
        }

        $invoice->update(['invoice_status' => $toStatus]);

        return $invoice;
    }
}