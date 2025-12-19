<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Employee\App\Models\Employee;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeePayrollComponent extends Model
{
    use LogsActivity;
    
    protected $fillable = ['employee_id', 'payroll_component_id', 'amount'];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('payroll_definition');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function component()
    {
        return $this->belongsTo(PayrollComponent::class, 'payroll_component_id');
    }
}
