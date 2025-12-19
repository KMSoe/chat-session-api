<?php
namespace Modules\CRM\App\Repositories;

use App\Http\Services\ApproverService;
use App\Models\CodePrefix;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\CRM\App\Http\Resources\QuotationBoardResource;
use Modules\CRM\App\Http\Resources\QuotationDetailsResource;
use Modules\CRM\App\Http\Resources\QuotationListingResource;
use Modules\CRM\App\Mails\QuotationMail;
use Modules\CRM\App\Models\Contact;
use Modules\CRM\App\Models\Quotation;
use Modules\Employee\App\Models\Employee;
use Modules\Notification\App\Repositories\NotificationRepository;
use Modules\Notification\App\Services\Impl\NotificationServiceImpl;
use PDF;

class QuotationRepo
{
    private ApproverService $approvalFlowService;

    public function __construct(ApproverService $approvalFlowService)
    {
        $this->approvalFlowService = $approvalFlowService;
    }

    /**
     * Get All Quotations.
     */
    public function getAll()
    {
        return Quotation::with('company', 'contact')->get();
    }

    /**
     * Get Quotations With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $quotations = Quotation::query();

        // Filter by search
        if (isset($request['search'])) {
            $quotations->where(function ($query) use ($request) {
                $query->where('quotation_number', 'like', '%' . $request['search'] . '%')
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
            $quotations->whereBetween('quotation_date', [$startDate, $endDate]);
        }

        // Price Filter
        if (isset($request['min_price']) && isset($request['max_price'])) {
            $quotations->whereBetween('grand_total', [$request['min_price'], $request['max_price']]);
        }

        // Status Filter
        if (isset($request['status']) && $request['status'] != null && $request['status'] != '') {
            $statuses = explode(',', $request['status']);
            $quotations->whereIn('quotation_status', $statuses);
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $quotations->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $quotations->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $quotations->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $quotations->get();

            return QuotationDetailsResource::collection($items);
        }

        $quotations = $quotations->with('company', 'contact')->paginate($perPage);

        $data = $quotations->getCollection()->map(function ($item) {
            return new QuotationListingResource($item);
        });

        return $quotations->setCollection($data);
    }

    public function getQuotationBoard($request)
    {
        $quotations = Quotation::query();

        if (isset($request['search'])) {
            $quotations->where(function ($query) use ($request) {
                $query->where('quotation_number', 'like', '%' . $request['search'] . '%')
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
            $quotations->whereBetween('quotation_date', [$startDate, $endDate]);
        }

        // Price Filter
        if (isset($request['min_price']) && isset($request['max_price'])) {
            $quotations->whereBetween('grand_total', [$request['min_price'], $request['max_price']]);
        }

        // Status Filter
        if (isset($request['status']) && $request['status'] != null && $request['status'] != '') {
            $statuses = explode(',', $request['status']);
            $quotations->whereIn('quotation_status', $statuses);
        }

        $quotations = $quotations->get();

        return new QuotationBoardResource($quotations);
    }

    /**
     * Get Quotation with id
     */
    public function get($id)
    {
        return Quotation::with(
            'company',
            'contact',
            'project',
            'currency',
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
        )->findOrFail($id);
    }

    /**
     * Create Quotation
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;
        $quotation = Quotation::create($data);
        $notifyEmployeeIds = [];

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $quotation->items()->create($itemData);
            }
        }

        if (isset($data['organization_signatures']) && is_array($data['organization_signatures'])) {
            foreach ($data['organization_signatures'] as $signatureData) {
                $signatureData['type'] = 'organization';
                $quotation->organizationSignatures()->create($signatureData);
                if (isset($signatureData['notify_signer']) && $signatureData['notify_signer'] == true && isset($signatureData['assigned_signer_id'])) {
                    $notifyEmployeeIds[] = $signatureData['assigned_signer_id'];
                }
            }
        }

        if (isset($data['customer_signatures']) && is_array($data['customer_signatures'])) {
            foreach ($data['customer_signatures'] as $signatureData) {
                $signatureData['type'] = 'customer';
                $quotation->customerSignatures()->create($signatureData);
            }
        }

        if (isset($data['communications']) && is_array($data['communications'])) {
            foreach ($data['communications'] as $contactId) {
                $quotation->emailCommunications()->create(['contact_id' => $contactId]);
            }
        }

        if (isset($data['files']) && is_array($data['files'])) {
            foreach ($data['files'] as $fileId) {
                $quotation->files()->create(['file_id' => $fileId]);
            }
        }

        if (isset($data['quotation_status']) && $data['quotation_status'] === 'pending_approval') {
            $this->approvalFlowService->createActionOwner('quotation', $quotation->id);
        }

        if (isset($data['quotation_status']) && $data['quotation_status'] === 'save_and_send') {
            $emailList = [];
            foreach ($quotation->emailCommunications as $communication) {
                $emailList[] = $communication->contact?->email;
            }
            $this->quotationMail($emailList, $quotation);
        }

        $this->createActivityLog($quotation, [
            'description' => 'Quotation is newly created',
        ]);

        if (! empty($notifyEmployeeIds)) {
            $this->sentNotification(
                $notifyEmployeeIds,
                [
                    'title'       => 'Quotation Signature Notification',
                    'description' => 'You have been assigned as a signer for quotation #' . $quotation->quotation_number,
                    'subject'     => 'Quotation Notification',
                    'type'        => 'quotation_notification',
                    'direction'   => 'quotation_approval',
                ]
            );
        }

        return $quotation->load(
            'company',
            'contact',
            'project',
            'currency',
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
     * Update Quotation
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $quotation          = Quotation::findOrFail($id);
        $notifyEmployeeIds  = [];

        $quotation->update($data);

        if (isset($data['items']) && is_array($data['items'])) {
            $quotation->items()->delete();
            foreach ($data['items'] as $itemData) {
                $quotation->items()->create($itemData);
            }
        }

        if (isset($data['organization_signatures']) && is_array($data['organization_signatures'])) {
            $quotation->organizationSignatures()->delete();
            foreach ($data['organization_signatures'] as $signatureData) {
                $signatureData['type'] = 'organization';
                $quotation->organizationSignatures()->create($signatureData);
                if (isset($signatureData['notify_signer']) && $signatureData['notify_signer'] == true && isset($signatureData['assigned_signer_id'])) {
                    $notifyEmployeeIds[] = $signatureData['assigned_signer_id'];
                }
            }
        }

        if (isset($data['customer_signatures']) && is_array($data['customer_signatures'])) {
            $quotation->customerSignatures()->delete();
            foreach ($data['customer_signatures'] as $signatureData) {
                $signatureData['type'] = 'customer';
                $quotation->customerSignatures()->create($signatureData);
            }
        }

        if (isset($data['communications']) && is_array($data['communications'])) {
            $quotation->emailCommunications()->delete();
            foreach ($data['communications'] as $contactId) {
                $quotation->emailCommunications()->create(['contact_id' => $contactId]);
            }
        }

        if (isset($data['files']) && is_array($data['files'])) {
            $quotation->files()->delete();
            foreach ($data['files'] as $fileId) {
                $quotation->files()->create(['file_id' => $fileId]);
            }
        }

        if (isset($data['quotation_status']) && $data['quotation_status'] === 'pending_approval') {
            $this->approvalFlowService->createActionOwner('quotation', $quotation->id);
        }

        if (isset($data['quotation_status']) && $data['quotation_status'] === 'save_and_send') {
            $emailList = [];
            foreach ($quotation->emailCommunications as $communication) {
                $emailList[] = $communication->contact?->email;
            }
            $this->quotationMail($emailList, $quotation);
        }

        $this->createActivityLog($quotation, [
            'description' => 'Quotation is edited',
        ]);

        if (! empty($notifyEmployeeIds)) {
            $this->sentNotification(
                $notifyEmployeeIds,
                [
                    'title'       => 'Quotation Signature Notification',
                    'description' => 'You have been assigned as a signer for quotation #' . $quotation->quotation_number,
                    'subject'     => 'Quotation Notification',
                    'type'        => 'quotation_notification',
                    'direction'   => 'quotation_approval',
                ]
            );
        }

        return $quotation->load(
            'company',
            'contact',
            'project',
            'currency',
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
        $causer              = auth()->user();
        $notificationService = new NotificationServiceImpl(new NotificationRepository());

        $data = [
            'title'        => $message['title'],
            'description'  => $message['description'],
            'type'         => $message['type'],
            'direction'    => $message['direction'],
            'event'        => 'created',
            'subject'      => $message['subject'],
            'causer'       => $causer,
            'send_to'      => 'custom',
            'employee_ids' => is_array($employeeIds) ? $employeeIds : [$employeeIds],
        ];

        $notificationService->save($data);
    }

    /**
     * Delete Quotation
     */
    public function delete($id)
    {
        $quotation = Quotation::findOrFail($id);
        $quotation->delete();
    }

    public function approvalStatusUpdate(array $data)
    {
        foreach ($data['ids'] as $id) {
            $quotation = Quotation::findOrFail($id);
            if ($data['status'] === 'approved') {
                $this->approvalFlowService->approve($quotation->id, auth()->user()->id, 'quotation', $data['comment'] ?? null);
            } else {
                $this->approvalFlowService->reject($quotation->id, auth()->user()->id, 'quotation', $data['comment'] ?? null);
            }

            $quotation->refresh();

            if ($quotation->status === 'approved' || $quotation->status === 'rejected') {
                $quotation->update([
                    'quotation_status' => $quotation->status,
                ]);
            }

            $this->createActivityLog($quotation, [
                'description' => 'Status is updated',
            ]);
        }
    }

    public function quotationStatusUpdate($id, array $data)
    {
        $quotation = Quotation::findOrFail($id);

        $quotation->update([
            'quotation_status' => $data['status'],
        ]);

        $this->createActivityLog($quotation, [
            'description' => 'Quotation status is updated to ' . $data['status'],
        ]);
    }

    public function createActivityLog($quotation, $data)
    {
        $data['description'] = $data['description'] ?? null;
        $data['action_by']   = auth()->user()->id ?? 0;

        return $quotation->activityLogs()->create($data);
    }

    public function getActivityLogs($quotationId)
    {
        $quotation = Quotation::findOrFail($quotationId);

        $activityLogs = $quotation->activityLogs()->get()->map(function ($log) {
            return [
                'id'          => $log->id,
                'description' => $log->description,
                'action_by'   => $log->actionBy?->name,
                'created_at'  => $log->created_at,
                'updated_at'  => $log->updated_at,
            ];
        });

        return $activityLogs;
    }

    public function getComments($quotationId)
    {
        $quotation = Quotation::findOrFail($quotationId);

        $comments = $quotation->comments()->get()->map(function ($comment) {
            return [
                'id'           => $comment->id,
                'comment'      => $comment->comment,
                'commented_by' => $comment->commentBy?->name,
                'created_at'   => $comment->created_at,
                'updated_at'   => $comment->updated_at,
            ];
        });

        return $comments;
    }

    public function addComment($quotationId, $data)
    {
        $quotation = Quotation::findOrFail($quotationId);

        $commentData = [
            'comment'      => $data['comment'],
            'commented_by' => auth()->user()->id,
        ];

        $comment = $quotation->comments()->create($commentData);

        return $comment;
    }

    public function updateComment($quotationId, $commentId, $data)
    {
        $quotation = Quotation::findOrFail($quotationId);

        $comment = $quotation->comments()->where('id', $commentId)->firstOrFail();

        $comment->update([
            'comment' => $data['comment'],
        ]);

        return $comment;
    }

    public function deleteComment($quotationId, $commentId)
    {
        $quotation = Quotation::findOrFail($quotationId);

        $comment = $quotation->comments()->where('id', $commentId)->firstOrFail();

        $comment->delete();
    }

    public function sentMail($quotationId, $data)
    {
        $quotation = Quotation::with(['files'])->findOrFail($quotationId);

        $this->quotationMail($data['to'], $quotation, $data);

        $this->createActivityLog($quotation, [
            'description' => 'Sent email is clicked and sent',
        ]);
    }

    private function quotationMail($emails, $quotation, $mailInfo = null)
    {
        foreach ($emails as $email) {
            Mail::to($email)->send(new QuotationMail($quotation, $mailInfo));
        }
    }

    private function getEmployee()
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return $employee;
    }

    public function getInitialData()
    {
        $hasApproval      = $this->checkApprovalConfig();
        $prefix           = CodePrefix::where('module_name', 'quotation')->first();
        $quotation_type   = $prefix && $prefix->type == 'automatic' ? 'automatic' : 'manual';
        $quotation_number = $this->generateQuotationNumber();

        return [
            'hasApproval'      => $hasApproval,
            'quotation_type'   => $quotation_type,
            'quotation_number' => $quotation_number,
        ];
    }

    public function checkApprovalConfig()
    {
        $employee = $this->getEmployee();
        return $this->approvalFlowService->hasApprovalConfig($employee->id, 'quotation');
    }

    public function generateQuotationNumber()
    {
        $config = CodePrefix::where('module_name', 'quotation')->first();

        if (! $config || $config->type !== 'automatic') {
            return null;
        }

        $prefix   = rtrim($config->prefix, '-') . '-';
        $nextBase = $config->next_number;

        $paddingLength = strlen($nextBase);

        $pattern = '/^' . preg_quote($prefix, '/') . '(\d{' . $paddingLength . '})$/';

        $quotations = Quotation::select('quotation_number')
            ->where('quotation_number', 'LIKE', $prefix . '%')
            ->get();

        $valid = $quotations->filter(function ($q) use ($pattern) {
            return preg_match($pattern, $q->quotation_number);
        });

        if ($valid->isEmpty()) {
            return $prefix . str_pad($nextBase, $paddingLength, '0', STR_PAD_LEFT);
        }

        $last = $valid->sortByDesc(function ($q) use ($prefix) {
            return (int) str_replace($prefix, '', $q->quotation_number);
        })->first();

        $currentNumber = (int) str_replace($prefix, '', $last->quotation_number);
        $nextNumber    = str_pad($currentNumber + 1, $paddingLength, '0', STR_PAD_LEFT);

        return $prefix . $nextNumber;
    }

    public function getSharedLinks($id)
    {
        $quotation = Quotation::findOrFail($id);

        return $quotation->invitationLinks()->get();
    }

    public function generateSharedLink($id, $data)
    {
        $quotation = Quotation::findOrFail($id);

        $token = Str::random(32);

        $sharedLink = $quotation->invitationLinks()->create([
            'expires_at'       => $data['expires_at'],
            'invitation_link'  => $data['invitation_link'] . '&token=' . $token,
            'invitation_token' => $token,
            'is_active'        => true,
        ]);

        return $sharedLink;
    }

    public function disableSharedLink($id, $data)
    {
        $quotation = Quotation::findOrFail($id);

        $quotation->invitationLinks()->whereIn('id', $data['ids'])->update([
            'is_active' => false,
        ]);
    }

    public function attachFile($id, $data)
    {
        $quotation = Quotation::findOrFail($id);

        if (! $quotation->files()->where('file_id', $data['file_id'])->exists()) {
            $quotation->files()->create(['file_id' => $data['file_id']]);
        }
    }

    public function deleteAttachedFile($id, $fileId)
    {
        $quotation = Quotation::findOrFail($id);

        $quotation->files()->where('file_id', $fileId)->delete();
    }

    public function signQuotation($id, $data)
    {
        $quotation = Quotation::findOrFail($id);

        $employee = $this->getEmployee();

        $assignedSigner = $quotation->organizationSignatures()->where('assigned_signer_id', $employee->id)->where('label', $data['signature_label'])->first();

        if (! $assignedSigner) {
            throw new \Exception('You are not authorized to sign this quotation.');
        }

        if (! $assignedSigner->label || $assignedSigner->label !== $data['signature_label']) {
            throw new \Exception('Invalid signature label for this signer. This assigner is assigned for label: ' . $assignedSigner->label);
        }

        // if(isset($assignedSigner) && $assignedSigner->label == $data['signature_label'] && $assignedSigner->signature_file_id !== null) {
        //     throw new \Exception('You have already signed this quotation with label: ' . $assignedSigner->label);
        // }

        $assignedSigner->where('label', $data['signature_label'])->update([
            'signature_file_id' => $data['signature_file_id'],
            'signed_at'         => now(),
        ]);

        $this->createActivityLog($quotation, [
            'description' => 'Quotation is signed by ' . $employee->name,
        ]);

        return $quotation;
    }

    public function updateClientQuotation($id, $data)
    {
        $quotation = Quotation::findOrFail($id);

        if (isset($data['action']) && $data['action'] === 'accepted') {
            $allSigned = $quotation->customerSignatures()->whereNull('signature_file_id')->count() === 0;
            if (! $allSigned) {
                throw new \Exception('All customer signatures must be signed before accepting the quotation.');
            }

            $this->createActivityLog($quotation, [
                'description' => 'Quotation is accepted and signed by client',
            ]);
        } else {
            $this->createActivityLog($quotation, [
                'description' => 'Quotation is declined by client',
            ]);
        }

        $quotation->update([
            'quotation_status' => $data['action'],
            'decline_reason'   => $data['action'] === 'declined' ? ($data['decline_reason'] ?? null) : null,
            'accepted_at'      => $data['action'] === 'accepted' ? now() : null,
            'declined_at'      => $data['action'] === 'declined' ? now() : null,
        ]);
    }

    public function signClientQuotation($id, $data)
    {
        $quotation = Quotation::findOrFail($id);

        $signer = $quotation->customerSignatures()->where('label', $data['signature_label'])->first();

        if (! $signer) {
            throw new \Exception('Invalid signature label.');
        }

        // if($signer->signature_file_id !== null) {
        //     throw new \Exception('This quotation has already been signed with label: ' . $signer->label);
        // }

        $signer->update([
            'signature_file_id' => $data['signature_file_id'],
            'signed_at'         => now(),
        ]);
    }

    public function getQuotationPublicDetails($id, $data)
    {
        $quotation = Quotation::with(
            'company',
            'contact',
            'project',
            'currency',
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

        if (! $quotation) {
            throw new \Exception('Invalid or expired invitation link.');
        }

        return $quotation;
    }

    public function generatePdf($id)
    {
        $quotation = Quotation::with([
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

        $pdf = PDF::loadView('crm::pdf.quotation', ['quotation' => $quotation])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('enable_css_float', true)
            ->setOption('enable_html5_parser', true);

        $pdfname = 'quotation_' . $quotation->quotation_number . '.pdf';

        return $pdf->download($pdfname);
    }
}
