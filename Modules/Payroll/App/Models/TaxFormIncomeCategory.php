<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxFormIncomeCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['tax_form_id', 'prefix_code', 'name', 'sort_order', 'is_active'];

    public function taxFform()
    {
        return $this->belongsTo(TaxForm::class);
    }

    public function incomeAdditions()
    {
        return $this->belongsToMany(
            PayrollComponent::class,
            'tax_form_income_category_additions',
            'tax_form_income_category_id',
            'payroll_component_id'
        );
    }

    /**
     * Get payroll components for deductions.
     */
    public function incomeDeductions()
    {
        return $this->belongsToMany(
            PayrollComponent::class,
            'tax_form_income_category_deductions',
            'tax_form_income_category_id',
            'payroll_component_id'
        );
    }
}
