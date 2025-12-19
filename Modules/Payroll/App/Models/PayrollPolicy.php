<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Employee\App\Models\Employee;
use Spatie\Activitylog\Traits\LogsActivity;

class PayrollPolicy extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'payroll_policies';

    protected $fillable = [
        'code',
        'name',
        'pay_frequency',
        'pay_cycle_start_date',
        'pay_cycle_start_day', // weekly only ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']
        'prorata_calculation_formula', // monthly
        'paid_leave_pay', // weekly only
        'holiday_pay', // weekly only
        'weekoff_pay', // weekly only
        'is_active',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('payroll_policy');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'payroll_policy_id');
    }
}
