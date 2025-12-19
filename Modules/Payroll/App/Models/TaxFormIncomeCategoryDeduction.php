<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class TaxFormIncomeCategoryDeduction extends Model
{
    use LogsActivity;
    
    protected $table = 'tax_form_income_category_deductions';

    protected $fillable = [
        'tax_form_income_category_id',
        'payroll_component_id',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('tax_form_income_category_deductions');
    }

    /**
     * Get the income category that owns the deduction.
     */
    public function incomeCategory()
    {
        return $this->belongsTo(TaxFormIncomeCategory::class, 'tax_form_income_category_id');
    }

    /**
     * Get the payroll component for the deduction.
     */
    public function payrollComponent()
    {
        return $this->belongsTo(PayrollComponent::class);
    }
}
