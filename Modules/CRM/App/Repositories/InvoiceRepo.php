<?php

namespace Modules\CRM\App\Repositories;

use App\Http\Services\ApproverService;
use App\Models\CodePrefix;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\CRM\App\Http\Resources\InvoiceBoardResource;
use Modules\CRM\App\Http\Resources\InvoiceDetailsResource;
use Modules\CRM\App\Http\Resources\InvoiceListingResource;
use Modules\CRM\App\Models\Invoice;
use Modules\Employee\App\Models\Employee;
use Modules\Notification\App\Repositories\NotificationRepository;
use Modules\Notification\App\Services\Impl\NotificationServiceImpl;

class InvoiceRepo
{
    private ApproverService $approvalFlowService;

    public function __construct(ApproverService $approvalFlowService)
    {
        $this->approvalFlowService = $approvalFlowService;
    }

    /**
     * Get All Invoices.
     */
    public function getAll()
    {
        return Invoice::with('company', 'contact')->get();
    }

    /**
     * Get Invoices With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $invoices = Invoice::query();

        // Filter by search
        if(isset($request['search'])) 
        {
            $invoices->where(function ($query) use ($request) {
                $query->where('invoice_number', 'like', '%' . $request['search'] . '%')
                      ->orWhere('reference_number', 'like', '%' . $request['search'] . '%');
            });
        }

        // Date Range Filter
        if (!empty($request['start']) && !empty($request['end'])) {
            $startDate = Carbon::parse($request['start'])->startOfDay();
            $endDate = Carbon::parse($request['end'])->endOfDay();
        } elseif (!empty($request['month'])) {
            $monthInput = $request['month'];
            $monthNum = null;
            $year = null;
            if (preg_match('/^(\d{2})-(\d{4})$/', $monthInput, $matches)) {
                // Format: 08-2025
                $monthNum = (int)$matches[1];
                $year = (int)$matches[2];
            } elseif (preg_match('/^(\d{4})-(\d{2})$/', $monthInput, $matches)) {
                // Format: 2025-08
                $year = (int)$matches[1];
                $monthNum = (int)$matches[2];
            }
            if ($monthNum && $year) {
                $date = Carbon::create($year, $monthNum, 1);
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
            } else {
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
            }
        } else {
            $startDate = null;
            $endDate = null;
        }

        if ($startDate && $endDate) {
            $invoices->whereBetween('invoice_date', [$startDate, $endDate]);
        }

        // Price Filter
        if (isset($request['min_price']) && isset($request['max_price'])) {
            $invoices->whereBetween('grand_total', [$request['min_price'], $request['max_price']]);
        }

        // Status Filter
        if (isset($request['status']) && $request['status'] != null && $request['status'] != '') {
            $statuses = explode(',', $request['status']);
            $invoices->whereIn('invoice_status', $statuses);
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $invoices->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $invoices->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $invoices->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $invoices->get();

            return InvoiceDetailsResource::collection($items);
        }

        $invoices = $invoices->with('company', 'contact')->paginate($perPage);

        $data = $invoices->getCollection()->map(function ($item) {
            return new InvoiceListingResource($item);
        });

        return $invoices->setCollection($data);
    }

    public function getInvoiceBoard($request)
    {
        $invoices = Invoice::query();

        if(isset($request['search'])) 
        {
            $invoices->where(function ($query) use ($request) {
                $query->where('invoice_number', 'like', '%' . $request['search'] . '%')
                      ->orWhere('reference_number', 'like', '%' . $request['search'] . '%');
            });
        }

        // Date Range Filter
        if (!empty($data['start']) && !empty($data['end'])) {
            $startDate = Carbon::parse($data['start'])->startOfDay();
            $endDate = Carbon::parse($data['end'])->endOfDay();
        } elseif (!empty($data['month'])) {
            $monthInput = $data['month'];
            $monthNum = null;
            $year = null;
            if (preg_match('/^(\d{2})-(\d{4})$/', $monthInput, $matches)) {
                // Format: 08-2025
                $monthNum = (int)$matches[1];
                $year = (int)$matches[2];
            } elseif (preg_match('/^(\d{4})-(\d{2})$/', $monthInput, $matches)) {
                // Format: 2025-08
                $year = (int)$matches[1];
                $monthNum = (int)$matches[2];
            }
            if ($monthNum && $year) {
                $date = Carbon::create($year, $monthNum, 1);
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
            } else {
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
            }
        } else {
            $startDate = null;
            $endDate = null;
        }

        if ($startDate && $endDate) {
            $invoices->whereBetween('invoice_date', [$startDate, $endDate]);
        }

        // Price Filter
        if (isset($request['min_price']) && isset($request['max_price'])) {
            $invoices->whereBetween('grand_total', [$request['min_price'], $request['max_price']]);
        }

        // Status Filter
        if (isset($request['status']) && $request['status'] != null && $request['status'] != '') {
            $statuses = explode(',', $request['status']);
            $invoices->whereIn('invoice_status', $statuses);
        }

        $invoices = $invoices->get();

        return new InvoiceBoardResource($invoices);
    }

    /**
     * Get Invoice with id
     */
    public function get($id)
    {
        return Invoice::with(
            'company', 
            'contact.creditNote.currency', 
            'project',
            'currency',
            'paymentTerm',
            'itemTemplate',
            'tax',
            'salePerson',
            'organizationSignatures.assignedSigner',
            'organizationSignatures.signatureFile',
            'items.tax',
            'items.item',
            'actionOwners.actionOwner',
            'customerSignatures.signatureFile',
            'emailCommunications.contact',
            'files.file',
            'payments.receivedPayment',
            'creditNoteApplications.creditNote',
            'createdBy',
            'updatedBy'
        )->findOrFail($id);
    }

    /**
     * Create Invoice
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $data['discount_value'] = $data['discount_value'] ?? 0;
        $data['balance_due'] = $data['grand_total'] ?? 0;
        $invoice = Invoice::create($data);
        $notifyEmployeeIds = [];

        if(isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $invoice->items()->create($itemData);
            }
        }

        if(isset($data['organization_signatures']) && is_array($data['organization_signatures'])) {
            foreach ($data['organization_signatures'] as $signatureData) {
                $signatureData['type'] = 'organization';
                $invoice->organizationSignatures()->create($signatureData);
                if(isset($signatureData['notify_signer']) && $signatureData['notify_signer'] == true && isset($signatureData['assigned_signer_id'])) {
                    $notifyEmployeeIds[] = $signatureData['assigned_signer_id'];    
                }
            }
        }

        if(isset($data['customer_signatures']) && is_array($data['customer_signatures'])) {
            foreach ($data['customer_signatures'] as $signatureData) {
                $signatureData['type'] = 'customer';
                $invoice->customerSignatures()->create($signatureData);
            }
        }

        if(isset($data['communications']) && is_array($data['communications'])) {
            foreach ($data['communications'] as $contactId) {
                $invoice->emailCommunications()->create(['contact_id' => $contactId]);
            }
        }

        if(isset($data['files']) && is_array($data['files'])) {
            foreach ($data['files'] as $fileId) {
                $invoice->files()->create(['file_id' => $fileId]);
            }
        }

        if(isset($data['invoice_status']) && $data['invoice_status'] === 'pending_approval') {
            $this->approvalFlowService->createActionOwner('invoice', $invoice->id);
        }

        if(isset($data['invoice_status']) && $data['invoice_status'] === 'save_and_send') {
            $emailList = [];
            foreach ($invoice->emailCommunications as $communication) {
                $emailList[] = $communication->contact?->email;
            }
            $this->invoiceMail($emailList, $invoice);
        }

        $this->createActivityLog($invoice, [
            'description' => 'Invoice is newly created',
        ]);

        if(!empty($notifyEmployeeIds)) {
            $this->sentNotification(
                $notifyEmployeeIds,
                [
                    'title'       => 'Invoice Signature Notification',
                    'description' => 'You have been assigned as a signer for invoice #' . $invoice->invoice_number,
                    'subject'     => 'Invoice Notification',
                    'type'        => 'invoice_notification',
                    'direction'   => 'invoice_approval',
                ]
            );
        }

        return $invoice->load(
            'company', 
            'contact', 
            'project',
            'currency',
            'paymentTerm',
            'itemTemplate',
            'tax',
            'salePerson',
            'organizationSignatures.assignedSigner',
            'organizationSignatures.signatureFile',
            'items.tax',
            'items.item',
            'actionOwners.actionOwner',
            'customerSignatures.signatureFile',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy'
        );
    }
    
    /**
     * Update Invoice
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $invoice = Invoice::findOrFail($id);
        $data['balance_due'] = $data['grand_total'] !== $invoice->grand_total ? $data['grand_total'] : $invoice->balance_due;
        $notifyEmployeeIds = [];

        $invoice->update($data);

        if(isset($data['items']) && is_array($data['items'])) {
            $invoice->items()->delete();
            foreach ($data['items'] as $itemData) {
                $invoice->items()->create($itemData);
            }
        }

        if(isset($data['organization_signatures']) && is_array($data['organization_signatures'])) {
            $invoice->organizationSignatures()->delete();
            foreach ($data['organization_signatures'] as $signatureData) {
                $signatureData['type'] = 'organization';
                $invoice->organizationSignatures()->create($signatureData);
                if(isset($signatureData['notify_signer']) && $signatureData['notify_signer'] == true && isset($signatureData['assigned_signer_id'])) {
                    $notifyEmployeeIds[] = $signatureData['assigned_signer_id'];
                }
            }
        }

        if(isset($data['customer_signatures']) && is_array($data['customer_signatures'])) {
            $invoice->customerSignatures()->delete();
            foreach ($data['customer_signatures'] as $signatureData) {
                $signatureData['type'] = 'customer';
                $invoice->customerSignatures()->create($signatureData);
            }
        }

        if(isset($data['communications']) && is_array($data['communications'])) {
            $invoice->emailCommunications()->delete();
            foreach ($data['communications'] as $contactId) {
                $invoice->emailCommunications()->create(['contact_id' => $contactId]);
            }
        }

        if(isset($data['files']) && is_array($data['files'])) {
            $invoice->files()->delete();
            foreach ($data['files'] as $fileId) {
                $invoice->files()->create(['file_id' => $fileId]);
            }
        }

        if(isset($data['invoice_status']) && $data['invoice_status'] === 'pending_approval') {
            $this->approvalFlowService->createActionOwner('invoice', $invoice->id);
        }

        if(isset($data['invoice_status']) && $data['invoice_status'] === 'save_and_send') {
            $emailList = [];
            foreach ($invoice->emailCommunications as $communication) {
                $emailList[] = $communication->contact?->email;
            }
            $this->invoiceMail($emailList, $invoice);
        }

        $this->createActivityLog($invoice, [
            'description' => 'Invoice is edited',
        ]);

        if(!empty($notifyEmployeeIds)) {
            $this->sentNotification(
                $notifyEmployeeIds,
                [
                    'title'       => 'Invoice Signature Notification',
                    'description' => 'You have been assigned as a signer for invoice #' . $invoice->invoice_number,
                    'subject'     => 'Invoice Notification',
                    'type'        => 'invoice_notification',
                    'direction'   => 'invoice_approval',
                ]
            );
        }

        return $invoice->load(
            'company', 
            'contact', 
            'project',
            'currency',
            'paymentTerm',
            'itemTemplate',
            'tax',
            'salePerson',
            'organizationSignatures.assignedSigner',
            'organizationSignatures.signatureFile',
            'items.tax',
            'items.item',
            'actionOwners.actionOwner',
            'customerSignatures.signatureFile',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy'
        );
    }

    public function sentNotification($employeeIds, $message)
    {
        $causer = auth()->user();
        $notificationService = new NotificationServiceImpl(new NotificationRepository());
        
        $data = [
            'title'       => $message['title'],
            'description' => $message['description'],
            'type'        => $message['type'],
            'direction'   => $message['direction'],
            'event'       => 'created',
            'subject'     => $message['subject'],
            'causer'      => $causer,
            'send_to'     => 'custom',
            'employee_ids' => is_array($employeeIds) ? $employeeIds : [$employeeIds],
        ];
        
        $notificationService->save($data);
    }

    /**
     * Delete Invoice
     */
    public function delete($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
    }

    public function approvalStatusUpdate(array $data)
    {
        foreach ($data['ids'] as $id) {
            $invoice = Invoice::findOrFail($id);
            if ($data['status'] === 'approved') {
                $this->approvalFlowService->approve($invoice->id, auth()->user()->id, 'invoice', $data['comment'] ?? null);
            } else {
                $this->approvalFlowService->reject($invoice->id, auth()->user()->id, 'invoice', $data['comment'] ?? null);
            }

            $invoice->refresh();

            if ($invoice->status === 'approved' || $invoice->status === 'rejected') {
                $invoice->update([
                    'invoice_status' => $invoice->status,
                ]);
            }

            $this->createActivityLog($invoice, [
                'description' => 'Status is updated',
            ]);
        }
    }

    public function createActivityLog($invoice, $data)
    {
        $data['description'] = $data['description'] ?? null;
        $data['action_by'] = auth()->user()->id ?? 0;

        return $invoice->activityLogs()->create($data);
    }

    public function getActivityLogs($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $activityLogs = $invoice->activityLogs()->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'description' => $log->description,
                'action_by' => $log->actionBy?->name,
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ];
        });

        return $activityLogs;
    }

    public function getComments($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $comments = $invoice->comments()->get()->map(function ($comment) {
            return [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'commented_by' => $comment->commentBy?->name,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ];
        });

        return $comments;
    }

    public function addComment($invoiceId, $data)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $commentData = [
            'comment'   => $data['comment'],
            'commented_by' => auth()->user()->id,
        ];

        $comment = $invoice->comments()->create($commentData);

        return $comment;
    }

    public function updateComment($invoiceId, $commentId, $data)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $comment = $invoice->comments()->where('id', $commentId)->firstOrFail();
        
        $comment->update([
            'comment' => $data['comment'],
        ]);

        return $comment;
    }

    public function deleteComment($invoiceId, $commentId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $comment = $invoice->comments()->where('id', $commentId)->firstOrFail();

        $comment->delete();
    }
    
    public function sentMail($invoiceId, $data)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $this->invoiceMail($data['emails'], $invoice, $data['mail_info'] ?? null);

        $this->createActivityLog($invoice, [
            'description' => 'Sent email is clicked and sent',
        ]);
    }

    private function invoiceMail($emails, $invoice, $mailInfo = null)
    {
        // Implement email sending logic
    }

    private function getEmployee()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return $employee;
    }

    public function getInitialData()
    {
        $hasApproval = $this->checkApprovalConfig();
        $prefix = CodePrefix::where('module_name', 'invoice')->first();
        $invoice_type = $prefix && $prefix->type == 'automatic' ? 'automatic' : 'manual';
        $invoice_number = $this->generateInvoiceNumber();

        return [
            'hasApproval' => $hasApproval,
            'invoice_type' => $invoice_type,
            'invoice_number' => $invoice_number,
        ];
    }

    public function checkApprovalConfig()
    {
        $employee = $this->getEmployee();
        return $this->approvalFlowService->hasApprovalConfig($employee->id, 'invoice');
    }

    public function generateInvoiceNumber()
    {
        $config = CodePrefix::where('module_name', 'invoice')->first();

        if (!$config || $config->type !== 'automatic') {
            return null;
        }

        $prefix = rtrim($config->prefix, '-') . '-';
        $nextBase = $config->next_number;

        $paddingLength = strlen($nextBase);

        $pattern = '/^' . preg_quote($prefix, '/') . '(\d{' . $paddingLength . '})$/';

        $invoices = Invoice::select('invoice_number')
            ->where('invoice_number', 'LIKE', $prefix . '%')
            ->get();

        $valid = $invoices->filter(function ($q) use ($pattern) {
            return preg_match($pattern, $q->invoice_number);
        });

        if ($valid->isEmpty()) {
            return $prefix . str_pad($nextBase, $paddingLength, '0', STR_PAD_LEFT);
        }

        $last = $valid->sortByDesc(function ($q) use ($prefix) {
            return (int) str_replace($prefix, '', $q->invoice_number);
        })->first();

        $currentNumber = (int) str_replace($prefix, '', $last->invoice_number);
        $nextNumber = str_pad($currentNumber + 1, $paddingLength, '0', STR_PAD_LEFT);

        return $prefix . $nextNumber;
    }

    public function getSharedLinks($id)
    {
        $invoice = Invoice::findOrFail($id);

        return $invoice->invitationLinks()->get();
    }

    public function generateSharedLink($id, $data)
    {
        $invoice = Invoice::findOrFail($id);

        $token = Str::random(32);

        $sharedLink = $invoice->invitationLinks()->create([
            'expires_at' => $data['expires_at'],
            'invitation_link' => $data['invitation_link'].'&token='.$token,
            'invitation_token' => $token,
            'is_active' => true,
        ]);

        return $sharedLink;
    }

    public function disableSharedLink($id, $data)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->invitationLinks()->whereIn('id', $data['ids'])->update([
            'is_active' => false,
        ]);
    }

    public function attachFile($id, $data)
    {
        $invoice = Invoice::findOrFail($id);

        if (!$invoice->files()->where('file_id', $data['file_id'])->exists()) {
            $invoice->files()->create(['file_id' => $data['file_id']]);
        }
    }

    public function deleteAttachedFile($id, $fileId)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->files()->where('file_id', $fileId)->delete();
    }

    public function signInvoice($id, $data)
    {
        $invoice = Invoice::findOrFail($id);

        $employee = $this->getEmployee();

        $assignedSigner = $invoice->organizationSignatures()->where('assigned_signer_id', $employee->id)->where('label', $data['signature_label'])->first();

        if (!$assignedSigner) {
            throw new \Exception('You are not authorized to sign this invoice.');
        }

        if (!$assignedSigner->label || $assignedSigner->label !== $data['signature_label']) {
            throw new \Exception('Invalid signature label for this signer. This assigner is assigned for label: ' . $assignedSigner->label);
        }

        // if(isset($assignedSigner) && $assignedSigner->label == $data['signature_label'] && $assignedSigner->signature_file_id !== null) {
        //     throw new \Exception('You have already signed this invoice with label: ' . $assignedSigner->label);
        // }

        $assignedSigner->where('label', $data['signature_label'])->update([
            'signature_file_id' => $data['signature_file_id'],
            'signed_at' => now(),
        ]);

        $this->createActivityLog($invoice, [
            'description' => 'Invoice is signed by ' . $employee->first_name . ' ' . $employee->last_name,
        ]);

        return $invoice;
    }

    public function signClientInvoice($id, $data)
    {
        $invoice = Invoice::findOrFail($id);

        $signer = $invoice->customerSignatures()->where('label', $data['signature_label'])->first();

        if (!$signer) {
            throw new \Exception('Invalid signature label.');
        }

        // if($signer->signature_file_id !== null) {
        //     throw new \Exception('This invoice has already been signed with label: ' . $signer->label);
        // }

        $signer->update([
            'signature_file_id' => $data['signature_file_id'],
            'signed_at' => now(),
        ]);
    }

    public function getInvoicePublicDetails($id, $data)
    {
        $invoice = Invoice::with(
            'company', 
            'contact', 
            'project',
            'itemTemplate',
            'currency',
            'paymentTerm',
            'tax',
            'salePerson',
            'organizationSignatures.assignedSigner',
            'organizationSignatures.signatureFile',
            'items.tax',
            'items.item',
            'actionOwners.actionOwner',
            'customerSignatures.signatureFile',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy'
        )->where('id', $id)
         ->whereHas('invitationLinks', function ($query) use ($data) {
             $query->where('invitation_token', $data['token'])
                   ->where('is_active', true)
                   ->where(function ($q) {
                       $q->whereNull('expires_at')
                         ->orWhere('expires_at', '>', now());
                   });
         })
        ->first();

        if (!$invoice) {
            throw new \Exception('Invalid or expired invitation link.');
        }

        return $invoice;
    }

    public function addExpectedPaymentDate($id, $data)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->update([
            'expected_payment_date' => $data['expected_payment_date'],
        ]);

        if(isset($data['reason']) && $data['reason'] != null) {
            $this->createActivityLog($invoice, [
                'description' => 'Expected payment date is updated. Reason: ' . $data['reason'],
            ]);
        } else {
            $this->createActivityLog($invoice, [
                'description' => 'Expected payment date is updated.',
            ]);
        }

        return $invoice;
    }

    public function markAsVoid($id, $data)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->update([
            'void_date' => now(),
            'invoice_status' => 'void',
        ]);

        if(isset($data['reason']) && $data['reason'] != null) {
            $this->createActivityLog($invoice, [
                'description' => 'Invoice is marked as void. Reason: ' . $data['reason'],
            ]);
        } else {
            $this->createActivityLog($invoice, [
                'description' => 'Invoice is marked as void.',
            ]);
        }

        return $invoice;
    }

    public function markAsWriteOff($id, $data)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->update([
            'write_off_date' => $data['date'] ?? now(),
            'invoice_status' => 'write_off',
        ]);

        if(isset($data['reason']) && $data['reason'] != null) {
            $this->createActivityLog($invoice, [
                'description' => 'Invoice is marked as write off. Reason: ' . $data['reason'],
            ]);
        } else {
            $this->createActivityLog($invoice, [
                'description' => 'Invoice is marked as write off.',
            ]);
        }

        return $invoice;
    }

    public function cancelWriteOff($id, $data)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->update([
            'write_off_date' => null,
            'invoice_status' => 'unpaid',
        ]);

        $this->createActivityLog($invoice, [
            'description' => 'Invoice write off is cancelled.',
        ]);

        return $invoice;
    }

    public function generatePdf($id)
    {
        $invoice = Invoice::with([
            'company',
            'contact',
            'currency',
            'salePerson',
            'items.item',
            'items.tax',
            'tax',
            'organizationSignatures.assignedSigner',
            'organizationSignatures.signatureFile',
            'customerSignatures.signatureFile'
        ])->findOrFail($id);

        $pdf = PDF::loadView('crm::pdf.invoice', ['invoice' => $invoice])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('enable_css_float', true)
            ->setOption('enable_html5_parser', true);

        $pdfname = 'invoice_' . $invoice->invoice_number . '.pdf';

        return $pdf->download($pdfname);
    }
}