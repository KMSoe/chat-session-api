<?php
namespace Modules\Payroll\App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Employee\App\Models\Employee;
use Modules\Payroll\App\Enums\PayFrequencyTypes;
use Modules\Payroll\App\Enums\PayrollComponentTypes;
use Nnjeim\World\Models\Currency;
use Spatie\Activitylog\Traits\LogsActivity;

class Payroll extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'employee_id',
        'payroll_month',
        'pay_frequency',
        'pay_cycle_start_date',
        'pay_cycle_end_date',
        'daily_pay_dates',
        'hourly_pay_dates',
        'currency_id',
        'hours_worked',
        'hourly_rate',
        'daily_rate',
        'pay_amount',
        'basic_salary',
        'gross_salary',
        'total_deductions',
        'net_pay',
        'status',
        'pay_date',
        'updated_by',
    ];

    protected $casts = [
        'daily_pay_dates'  => 'array',
        'hourly_pay_dates' => 'array',
    ];

    protected $appends = ['pay_period'];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('payroll');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function entries()
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function earnings()
    {
        return $this->hasMany(PayrollEntry::class)->where('type', PayrollComponentTypes::EARNING);
    }

    public function deductions()
    {
        return $this->hasMany(PayrollEntry::class)->where('type', PayrollComponentTypes::DEDUCTION);
    }

    public function slip()
    {
        return $this->hasOne(PayrollSlip::class);
    }

    public function overtimeSummary()
    {
        return $this->hasOne(PayrollOvertimeSummary::class);
    }

    public function absentSummary()
    {
        return $this->hasOne(PayrollAbsentSummary::class);
    }

    public function unpaidLeaveSummary()
    {
        return $this->hasOne(PayrollUnpaidLeaveSummary::class);
    }

    public function leaveSummary()
    {
        return $this->hasOne(PayrollLeaveSummary::class);
    }

    public function getTotalEarningsAttribute()
    {
        return $this->earnings->where('affects_net_pay', true)->sum('amount');
    }

    public function getTotalDeductionsAttribute()
    {
        return $this->deductions->where('affects_net_pay', true)->sum('amount');
    }

    public function getNetPayAttribute()
    {
        return $this->gross_salary - $this->total_deductions;
    }

    public function getPayPeriodAttribute()
    {
        $pay_frequency = $this->pay_frequency;
        $result        = '';

        if ($pay_frequency == PayFrequencyTypes::MONTHLY->value) {
            $result = Carbon::parse($this->payroll_month)->format('F Y');
        } else if ($pay_frequency == PayFrequencyTypes::WEEKLY->value) {
            $result = Carbon::parse($this->pay_cycle_start_date)->format('d M Y') . ' - ' . Carbon::parse($this->pay_cycle_end_date)->format('d M Y');
        } else if ($pay_frequency == PayFrequencyTypes::DAILY->value) {
            $formatted = array_map(function ($date) {
                return Carbon::parse($date)->format('d M Y');
            }, $this->daily_pay_dates);

            $result = implode(', ', $formatted);
        } else if ($pay_frequency == PayFrequencyTypes::HOURLY->value) {
            $formatted = array_map(function ($date) {
                return Carbon::parse($date)->format('d M Y');
            }, $this->hourly_pay_dates);

            $result = implode(', ', $formatted);
        }

        return $result;
    }
}
