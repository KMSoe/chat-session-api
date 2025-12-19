<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Employee\App\Models\Employee;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeTaxCalculation extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'tax_calculation_id',
        'employee_id',
        'wholly_or_partly_paid_either',
        'non_hong_kong_company_name',
        'address',
        'amount',
        'remarks',
        'is_locked',
    ];

    protected $casts = [
        'wholly_or_partly_paid_either' => 'boolean',
        'is_locked'                    => 'boolean',
    ];

    protected $appends = ['total_income'];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('tax_calculation');
    }

    // Relationships
    public function taxCalculation()
    {
        return $this->belongsTo(TaxCalculation::class, 'tax_calculation_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function incomeDetails()
    {
        return $this->hasMany(EmployeeTaxCalculationIncomeDetail::class, 'employee_tax_calculation_id');
    }

    public function getTotalIncomeAttribute()
    {
        return $this->incomeDetails->sum('amount');
    }
}
