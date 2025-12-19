<?php

namespace Modules\Payroll\App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Employee\App\Models\Employee;
use Modules\Payroll\Database\factories\AdwOpeningBalanceFactory;

class AdwOpeningBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'effective_date',
        'from_date',
        'to_date',
        'total_wages_in_period',
        'total_worked_days_in_period',
        'adjustment_reason',
        'created_by',
        'updated_by'
    ];

     protected $casts = [
        'effective_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    protected $appends = ['average_daily_rate_adw'];

    public function getAverageDailyRateAdwAttribute()
    {
        return round($this->total_wages_in_period / $this->total_worked_days_in_period, 2);
    }

    public function getReferencePeriodAttribute()
    {
        return Carbon::parse($this->from_date)->format('d-M-Y') . ' - ' . Carbon::parse($this->to_date)->format('d-M-Y');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
