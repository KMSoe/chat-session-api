<?php
namespace Modules\Payroll\App\Services;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Attendance\Services\PayrollAttendanceService;
use Modules\Employee\App\Models\Employee;
use Modules\Leave\App\Repositories\LeaveRequestRepository;
use Modules\Notification\App\Services\Impl\NotificationServiceImpl;
use Modules\Notification\App\Services\NotificationService;
use Modules\OverTime\App\Repositories\OverTimeRepository;
use Modules\Payroll\App\Repositories\PayrollRepository;
use Modules\Payroll\App\Repositories\PayrollSlipRepository;
use Modules\Storage\App\Classes\ObjectStorage;

class PayrollSlipService
{
    protected $payrollRepository;
    protected PayrollSlipRepository $payrollSlipRepository;
    private OverTimeRepository $overTimeRepository;
    private LeaveRequestRepository $leaveRequestRepository;
    private PayrollAttendanceService $payrollAttendanceService;
    private EmployeeEnrollmentService $employeeEnrollmentService;
    private NotificationService $notificationService;
    private ObjectStorage $objectStorage;

    public function __construct(
        PayrollRepository $payrollRepository,
        PayrollSlipRepository $payrollSlipRepository,
        OverTimeRepository $overTimeRepository,
        LeaveRequestRepository $leaveRequestRepository,
        PayrollAttendanceService $payrollAttendanceService,
        EmployeeEnrollmentService $employeeEnrollmentService,
        NotificationServiceImpl $notificationService,
        ObjectStorage $objectStorage
    ) {
        $this->payrollRepository         = $payrollRepository;
        $this->payrollSlipRepository     = $payrollSlipRepository;
        $this->overTimeRepository        = $overTimeRepository;
        $this->leaveRequestRepository    = $leaveRequestRepository;
        $this->payrollAttendanceService  = $payrollAttendanceService;
        $this->employeeEnrollmentService = $employeeEnrollmentService;
        $this->notificationService       = $notificationService;
        $this->objectStorage             = $objectStorage;
    }

    public function previewSlip($id)
    {
        return $this->payrollSlipRepository->previewSlip($id);
    }

    public function downloadSlip($id)
    {
        return $this->payrollSlipRepository->downloadSlip($id);
    }

    public function sendSlip($id)
    {
        $payroll      = $this->payrollRepository->findById($id);
        $target_month = Carbon::parse($payroll->payroll_month)->format('M Y');

        $noti_data = [
            'title'        => "Your Salary has been transferred",
            'description'  => "Your <b>salary</b> for <b>$target_month</b> has been transferred.",
            'type'         => 'payroll',
            'direction'    => 'profile_payroll',
            'event'        => 'created',
            'causer'       => auth()->user(),
            'subject'      => $payroll,
            'send_to'      => 'custom',
            'employee_ids' => [$payroll->employee_id],
        ];

        $this->notificationService->save($noti_data);
        $employee_work_email = $payroll->employee?->email;

        try {
            if ($employee_work_email) {
                $company_info = $this->payrollSlipRepository->getCompanyInfo();

                $this->payrollSlipRepository->sendSlip($payroll, $company_info['company'], $company_info['logoBase64']);
            }
        } catch (\Exception $e) {
            Log::error("payroll_email_send_error", ["message" => $e->getMessage()]);
            Log::error("payroll_email_send_error", ["message" => "Error in mail sending for sending payroll-slip code: " . $payroll->id]);
            throw $e;
        }
    }

    public function sendMultiplePayrolls($ids)
    {
        $company    = Company::with(['logoFile'])->first();
        $logoFile   = $this->objectStorage->getFile($company->logoFile?->path);
        $mimeType   = $this->objectStorage->getMimeType($company->logoFile?->path);
        $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoFile);
        $payrolls   = $this->payrollRepository->findByMultipleIds($ids);

        foreach ($payrolls as $key => $payroll) {
            $target_month = Carbon::parse($payroll->payroll_month)->format('M Y');

            $noti_data = [
                'title'        => "Your Salary has been transferred",
                'description'  => "Your <b>salary</b> for <b>$target_month</b> has been transferred.",
                'type'         => 'payroll',
                'direction'    => 'profile_payroll',
                'event'        => 'created',
                'causer'       => auth()->user(),
                'subject'      => $payroll,
                'send_to'      => 'custom',
                'employee_ids' => [$payroll->employee_id],
            ];

            $this->notificationService->save($noti_data);
            $employee_work_email = $payroll->employee->email;

            try {
                if ($employee_work_email) {
                    $company_info = $this->payrollSlipRepository->getCompanyInfo();

                    $this->payrollSlipRepository->sendSlip($payroll, $company_info['company'], $company_info['logoBase64']);
                }
            } catch (\Exception $e) {
                Log::error("payroll_email_send_error", ["message" => $e->getMessage()]);
                Log::error("payroll_email_send_error", ["message" => "Error in mail sending for sending payroll-slip code: " . $payroll->id]);
                throw $e;
            }
        }
    }
}
