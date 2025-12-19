<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeTaxCalculationIncomeDetail extends Model
{
    use LogsActivity;

    protected $fillable = [
        'tax_calculation_id',
        'employee_tax_calculation_id',
        'tax_form_income_category_id',
        'amount',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('tax_calculation_income');
    }

    // Relationships
    public function employeeTaxCalculation()
    {
        return $this->belongsTo(EmployeeTaxCalculation::class, 'employee_tax_calculation_id');
    }

    public function taxCalculation()
    {
        return $this->belongsTo(TaxCalculation::class, 'tax_calculation_id');
    }

    public function taxFormIncomeCategory()
    {
        return $this->belongsTo(TaxFormIncomeCategory::class, 'tax_form_income_category_id');
    }
}
