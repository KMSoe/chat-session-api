<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Leave\App\Models\LeaveType;

class AverageDailyWagePayrollComponent extends Model
{
    protected $table = 'average_daily_wage_exclude_leave_types';

    protected $fillable = [
        'average_daily_wage_id',
        'leave_type_id',
    ];

    public function averageDailyWage()
    {
        return $this->belongsTo(AverageDailyWage::class, 'average_daily_wage_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
