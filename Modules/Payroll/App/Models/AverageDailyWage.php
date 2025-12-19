<?php
namespace Modules\Payroll\App\Models;

use App\Trait\HasApplicableScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Leave\App\Models\LeaveType;
use Spatie\Activitylog\Traits\LogsActivity;

class AverageDailyWage extends Model
{
    use SoftDeletes, LogsActivity;
    use HasApplicableScope;

    protected $table = 'average_daily_wages';

    protected $fillable = [
        'exclude_rest_days_from_adw',
        'exclude_holidays_from_adw',
        'minimum_employment_period_per_week',
        'minimum_employment_period_per_month',
        'remarks',
        'is_active',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('average_daily_wage');
    }

    /**
     * Relationship with payroll components.
     */
    public function payrollComponents()
    {
        return $this->belongsToMany(
            PayrollComponent::class,
            'average_daily_wage_payroll_components',
            'average_daily_wage_id',
            'payroll_component_id'
        );
    }

    /**
     * Relationship with leave types.
     */
    public function excludedLeaveTypes()
    {
        return $this->belongsToMany(
            LeaveType::class,
            'average_daily_wage_exclude_leave_types',
            'average_daily_wage_id',
            'leave_type_id'
        );
    }

    public function applicableTos()
    {
        return $this->hasMany(AverageDailyWageApplicableTo::class, 'average_daily_wage_id');
    }
}
