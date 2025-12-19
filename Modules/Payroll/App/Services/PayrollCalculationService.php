<?php
namespace Modules\Payroll\App\Services;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Attendance\Services\PayrollAttendanceService;
use Modules\Employee\App\Models\Employee;
use Modules\Leave\App\Repositories\LeaveRequestRepository;
use Modules\Notification\App\Services\Impl\NotificationServiceImpl;
use Modules\Notification\App\Services\NotificationService;
use Modules\OverTime\App\Repositories\OverTimeRepository;
use Modules\Payroll\App\Emails\SendPayrollMail;
use Modules\Payroll\App\Enums\PayFrequencyTypes;
use Modules\Payroll\App\Enums\PayrollComponentScopeModes;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Modules\Payroll\App\Enums\ProrataSalaryDaysInMonthTypes;
use Modules\Payroll\App\Helpers\PayrollHelper;
use Modules\Payroll\App\Models\EmployeePayrollComponent;
use Modules\Payroll\App\Models\Payroll;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\Repositories\PayrollRepository;
use Modules\Payroll\App\Repositories\PayrollSlipRepository;
use Modules\Storage\App\Classes\ObjectStorage;

class PayrollCalculationService
{

}