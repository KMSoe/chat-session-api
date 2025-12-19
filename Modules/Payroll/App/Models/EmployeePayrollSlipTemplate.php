<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Employee\App\Models\Employee;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeePayrollSlipTemplate extends Model
{
    use LogsActivity;

    protected $fillable = ['employee_id', 'payroll_slip_template_id'];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('payroll_slip_template');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollSlipTemplate()
    {
        return $this->belongsTo(PayrollSlipTemplate::class, 'payroll_slip_template_id');
    }
}
