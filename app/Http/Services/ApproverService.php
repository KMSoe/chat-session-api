<?php

namespace App\Http\Services;

use App\Models\Module;
use Modules\Employee\App\Models\Employee;
use Modules\ApprovalFlow\App\Models\ApprovalFlow;
use Modules\Attendance\App\Models\LateRequest;
use Modules\Attendance\App\Models\LateRequestActionOwner;
use Modules\Claim\App\Models\ClaimForm;
use Modules\Claim\App\Models\ClaimFormActionOwner;
use Modules\CRM\App\Models\Invoice;
use Modules\CRM\App\Models\InvoiceActionOwner;
use Modules\CRM\App\Models\Quotation;
use Modules\CRM\App\Models\QuotationActionOwner;
use Modules\Employee\App\Models\EmployeeExitForm;
use Modules\Employee\App\Models\EmployeeExitFormActionOwner;
use Modules\Leave\App\Models\LeaveRequest;
use Modules\Leave\App\Models\LeaveRequestActionOwner;
use Modules\Notification\App\Repositories\NotificationRepository;
use Modules\Notification\App\Services\Impl\NotificationServiceImpl;
use Modules\OverTime\App\Models\OverTime;
use Modules\OverTime\App\Models\OvertimeRequestActionOwner;

class ApproverService
{
    public function createActionOwner($moduleType, $requestId)
    {
        $requestModel = $this->getRequestModel($moduleType);
        $request = $requestModel::find($requestId);

        $employeeId = $request->employee_id;
        $employee = $this->getEmployee($employeeId);
        $moduleName = $this->getModuleName($moduleType);
        $moduleId   = Module::where('name', $moduleName)->first()?->id;

        if (!$moduleId) {
            throw new \Exception('Request created but no approval module found for this employee.');
        }

        $flowModule = $this->findApprovalFlowModule($employeeId, $moduleId);

        if (!$flowModule) {
            throw new \Exception('Request created but no approval flow found for this employee.');
        }

        foreach ($flowModule->approvers as $approver) {
            $model = $this->getActionOwnerModel($moduleType);
            $model::create([
                $this->getRequestForeignKey($moduleType) => $requestId,
                'action_owner_id' => $approver->employee_id,
                'level'           => $approver->level,
                'status'          => 'pending'
            ]);
        }

        $levelOneApprovers = $flowModule->approvers->where('level', 1)->pluck('employee_id')->toArray();
        $notificationModuleName = $this->getNotificationModuleName($moduleType);
        $this->sentNotification(
            $levelOneApprovers,
            [
                'title'       => $employee?->name . ' requested ' . ucfirst($notificationModuleName) .' request for approval',
                'description' => 'You have a new '. $notificationModuleName .' request to review.',
                'subject'     => ucfirst($notificationModuleName) .' Request Approval',
                'type'        => $notificationModuleName .'_notification',
                'direction'   => $notificationModuleName .'_approval'
            ]
        );
    }
    
    public function findApprovalFlowModule(int $employeeId, int $moduleId)
    {
        $scopes = $this->getEmployeeScopes($employeeId);

        $employeeFlow = ApprovalFlow::whereHas('applicableTos', function ($query) use ($employeeId) {
                $query->where('scope', 'employee')
                      ->where('target_id', $employeeId);
            })
            ->whereHas('approvalFlowModules', fn($q) => $q->where('module_id', $moduleId))
            ->with(['approvalFlowModules' => fn($q) => $q->where('module_id', $moduleId)->with('approvers')])
            ->first();

        if ($employeeFlow) {
            return $employeeFlow->approvalFlowModules->first();
        }

        // Then check group, designation, and department scopes
        foreach (['group', 'designation', 'department'] as $scope) {
            if (!empty($scopes[$scope])) {
                $flow = ApprovalFlow::whereHas('applicableTos', function ($query) use ($scope, $scopes) {
                        $query->where('scope', $scope)
                              ->whereIn('target_id', $scopes[$scope]);
                    })
                    ->whereHas('approvalFlowModules', fn($q) => $q->where('module_id', $moduleId))
                    ->with(['approvalFlowModules' => fn($q) => $q->where('module_id', $moduleId)->with('approvers')])
                    ->first();

                if ($flow) {
                    return $flow->approvalFlowModules->first();
                }
            }
        }

        return null;
    }

    public function getEmployeeScopes(int $employeeId): array
    {
        $employee = Employee::with(['designations', 'departments', 'groups'])->find($employeeId);

        return [
            'employee'    => [$employee->id],
            'designation' => $employee->designations?->pluck('id')->toArray() ?? [],
            'department'  => $employee->departments?->pluck('id')->toArray() ?? [],
            'group'       => $employee->groups?->pluck('id')->toArray() ?? [],
        ];
    }

    public function approve(int $requestId, int $approverId, string $moduleType, ?string $comment = null)
    {
        $actionOwner = $this->getActionOwner($requestId, $approverId, $moduleType);

        if (!$actionOwner || $actionOwner->status !== 'pending') {
            throw new \Exception('Approval not allowed or already processed for this level.');
        }

        $actionOwner->update([
            'status' => 'approved',
            'approved_at' => now(),
            'comment' => $comment,
            'actioned_by' => $approverId,
        ]);

        $currentLevel = $actionOwner->level;
        $model = $this->getActionOwnerModel($moduleType);
        $requestModel = $this->getRequestModel($moduleType);
        $request = $requestModel::findOrFail($requestId);

        $approvedInThisLevel = $model::where($this->getRequestForeignKey($moduleType), $requestId)
                                ->where('level', $currentLevel)
                                ->where('status', 'approved')
                                ->exists();

        if ($approvedInThisLevel) 
        {
            $nextLevelExists = $model::where($this->getRequestForeignKey($moduleType), $requestId)
                                ->where('level', $currentLevel + 1)
                                ->exists();

            if ($nextLevelExists) {
                $this->activateLevel($requestId, $moduleType, $currentLevel + 1, $request->employee_id);
                $request->update(['status' => 'in_progress']);
            } else {
                $request->update(['status' => 'approved']);
                $employeeIds = [$request->employee_id];
                $notificationModuleName = $this->getNotificationModuleName($moduleType);
                $this->sentNotification(
                    $employeeIds,
                    [
                        'title'       => ucfirst($notificationModuleName) .' Request Approved',
                        'description' => 'Your '. $notificationModuleName .' request has been approved.',
                        'subject'     => ucfirst($notificationModuleName) .' Request Approval',
                        'type'        => $notificationModuleName .'_notification',
                        'direction'   => $notificationModuleName .'_approval'
                    ]
                );
            }
        }
    }

    /**
     * Reject the request by a specific approver.
     *
     * @param int $requestId
     * @param int $approverId
     * @param string $moduleType
     * @param string|null $comment
     * @return void
     * @throws \Exception
     */
    public function reject(int $requestId, int $approverId, string $moduleType, ?string $comment = null)
    {
        $actionOwner = $this->getActionOwner($requestId, $approverId, $moduleType);
        if (!$actionOwner || $actionOwner->status !== 'pending') {
            throw new \Exception('Rejection not allowed or already processed.');
        }

        $actionOwner->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'comment' => $comment,
            'actioned_by' => $approverId,
        ]);

        $requestModel = $this->getRequestModel($moduleType);
        $request = $requestModel::findOrFail($requestId);
        $request->update(['status' => 'rejected']);
        $employeeIds = [$request->employee_id];
        $notificationModuleName = $this->getNotificationModuleName($moduleType);
        $this->sentNotification(
            $employeeIds,
            [
                'title'       => ucfirst($notificationModuleName) .' Request Rejected',
                'description' => 'Your '. $notificationModuleName .' request has been rejected.',
                'subject'     => ucfirst($notificationModuleName) .' Request Rejection',
                'type'        => $notificationModuleName .'_notification',
                'direction'   => $notificationModuleName .'_approval'
            ]
        );
    }

    /**
     * Helper to customize notification module name for sentNotification
     */
    protected function getNotificationModuleName(string $moduleType): string
    {
        $map = [
            'leave' => 'Leave',
            'late' => 'Late',
            'overtime' => 'Overtime',
            'claim_form' => 'Claim',
            'employee_exit_form' => 'Offboarding',
            'quotation' => 'Quotation',
            'invoice' => 'Invoice',
        ];
        return $map[$moduleType] ?? ucfirst($moduleType);
    }

    protected function getActionOwner(int $requestId, int $approverId, string $moduleType)
    {
        $model = $this->getActionOwnerModel($moduleType);

        $approverId = Employee::where('user_id', $approverId)->value('id');

        return $model::where('action_owner_id', $approverId)
            ->where($this->getRequestForeignKey($moduleType), $requestId)
            ->where('status', 'pending')
            ->first();
    }

    protected function getActionOwnersByLevel(int $requestId, string $moduleType, int $level)
    {
        $model = $this->getActionOwnerModel($moduleType);

        return $model::where($this->getRequestForeignKey($moduleType), $requestId)
            ->where('level', $level)
            ->get();
    }

    protected function getMaxLevel(int $requestId, string $moduleType)
    {
        $model = $this->getActionOwnerModel($moduleType);

        return (int) $model::where($this->getRequestForeignKey($moduleType), $requestId)
            ->max('level');
    }

    protected function getRequestModel(string $moduleType)
    {
        return match ($moduleType) {
            'leave' => LeaveRequest::class,
            'late' => LateRequest::class,
            'overtime' => OverTime::class,
            'claim_form' => ClaimForm::class,
            'employee_exit_form' => EmployeeExitForm::class,
            'quotation' => Quotation::class,
            'invoice' => Invoice::class,
            default => throw new \Exception('Unknown module type'),
        };
    }

    protected function getActionOwnerModel(string $moduleType)
    {
        return match ($moduleType) {
            'leave' => LeaveRequestActionOwner::class,
            'late' => LateRequestActionOwner::class,
            'overtime' => OvertimeRequestActionOwner::class,
            'claim_form' => ClaimFormActionOwner::class,
            'employee_exit_form' => EmployeeExitFormActionOwner::class,
            'quotation' => QuotationActionOwner::class,
            'invoice' => InvoiceActionOwner::class,
            default => throw new \Exception('Unknown module type'),
        };
    }

    protected function getRequestForeignKey(string $moduleType): string
    {
        return match ($moduleType) {
            'leave' => 'leave_request_id',
            'late' => 'late_request_id',
            'overtime' => 'over_time_id',
            'claim_form' => 'claim_form_id',
            'employee_exit_form' => 'employee_exit_form_id',
            'quotation' => 'quotation_id',
            'invoice' => 'invoice_id',
            default => throw new \Exception('Unknown module type'),
        };
    }

    protected function getModuleMapping(): array
    {
        return [
            'leave' => 'leave request',
            'late' => 'late request',
            'overtime' => 'overtime-request',
            'claim_form' => 'claim',
            'employee_exit_form' => 'offboarding',
            'quotation' => 'quotation',
            'invoice' => 'invoice',
        ];
    }

    protected function getModuleName(string $moduleType): string
    {
        $map = $this->getModuleMapping();

        if (! isset($map[$moduleType])) {
            throw new \Exception('Unknown module type');
        }

        return $map[$moduleType];
    }

    protected function activateLevel(int $requestId, string $moduleType, int $level, int $employeeId): void
    {
        $model = $this->getActionOwnerModel($moduleType);
        $employee = $this->getEmployee($employeeId);

        $approvers = $model::where($this->getRequestForeignKey($moduleType), $requestId)
                            ->where('level', $level)
                            ->where('status', 'pending')
                            ->get();

        if (!empty($approvers)) 
        {
            $notificationModuleName = $this->getNotificationModuleName($moduleType);
            $employeeIds = $approvers->pluck('action_owner_id')->toArray();
            $this->sentNotification(
                $employeeIds,
                [
                    'title'       => $employee?->name . ' requested ' . ucfirst($notificationModuleName) .' request for approval',
                    'description' => 'You have a new '. $notificationModuleName .' request to review.',
                    'subject'     => ucfirst($notificationModuleName) .' Request Approval',
                    'type'        => $notificationModuleName .'_notification',
                    'direction'   => $notificationModuleName .'_approval'
                ]
            );
        }
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

    public function getApprovalModules()
    {
        $moduleNames = array_values($this->getModuleMapping());
        $modules = Module::whereIn('name', $moduleNames)->get();
        return $modules;
    }

    public function getEmployee($id)
    {
        return Employee::find($id);
    }

    public function hasApprovalConfig($employeeId, $moduleType)
    {
        $moduleName = $this->getModuleName($moduleType);
        $moduleId   = Module::where('name', $moduleName)->first()?->id;

        if(!$moduleId) {
            throw new \Exception('Module is required to find approval flow module.');
        }
        
        $flowModule = $this->findApprovalFlowModule($employeeId, $moduleId);

        return $flowModule !== null ? true : false;
    }
}