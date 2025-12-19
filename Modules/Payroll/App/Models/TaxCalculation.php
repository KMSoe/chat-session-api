<?php
namespace Modules\Payroll\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class TaxCalculation extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'tax_form_id',
        'period_type',
        'date',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'date'       => 'date',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    protected $appends = ['period', 'number_of_people', 'total_income'];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('tax_calculation');
    }

    // Relationships
    public function taxForm()
    {
        return $this->belongsTo(TaxForm::class, 'tax_form_id');
    }

    public function employeeTaxCalculations()
    {
        return $this->hasMany(EmployeeTaxCalculation::class, 'tax_calculation_id');
    }

    // Accessor: formatted period
    public function getPeriodAttribute()
    {
        if ($this->period_type === 'single') {
            return Carbon::parse($this->date)->format('Y-m');
        }

        return Carbon::parse($this->start_date)->format('Y-m-d') . " ~ " .Carbon::parse($this->end_date)->format('Y-m-d');
    }

    // Accessor: number of people
    public function getNumberOfPeopleAttribute()
    {
        return $this->employeeTaxCalculations()->count();
    }

    // Accessor: total income
    public function getTotalIncomeAttribute()
    {
        return $this->employeeTaxCalculations()
            ->with('incomeDetails')
            ->get()
            ->sum(function ($employeeCalc) {
                return $employeeCalc->incomeDetails->sum('amount');
            });
    }
}
