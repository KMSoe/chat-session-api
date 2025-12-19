<?php

namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Payroll\Database\factories\AverageDailyWageExcludeLeaveTypeFactory;

class AverageDailyWageExcludeLeaveType extends Model
{
     protected $table = 'average_daily_wage_payroll_components';

    protected $fillable = [
        'average_daily_wage_id',
        'payroll_component_id',
    ];

    public function averageDailyWage()
    {
        return $this->belongsTo(AverageDailyWage::class, 'average_daily_wage_id');
    }

    public function payrollComponent()
    {
        return $this->belongsTo(PayrollComponent::class, 'payroll_component_id');
    }
}
