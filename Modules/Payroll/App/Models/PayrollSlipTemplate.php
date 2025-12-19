<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Storage\App\Models\File;

class PayrollSlipTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'logo_file_id',
        'name',
        'description',
        'is_default',
        'remove_components_with_zero_amount',
        'company_info_items',
        'employee_info_items',
        'enable_leave_section',
    ];

    protected $casts = [
        'is_default'                         => 'boolean',
        'remove_components_with_zero_amount' => 'boolean',
        'company_info_items'                 => 'array',
        'employee_info_items'                => 'array',
        'enable_leave_section'               => 'boolean',
    ];

    public function logoFile()
    {
    return $this->belongsTo(File::class, 'logo_file_id');
    }

    public function earningComponents()
    {
        return $this->belongsToMany(
            PayrollComponent::class,                    // related model
            'payroll_slip_template_earning_components', // pivot table
            'payroll_slip_template_id',                 // this model FK
            'payroll_component_id'                      // related model FK
        )->withPivot('order')                       // keep "order" field
            ->withTimestamps();
    }

    public function deductionComponents()
    {
        return $this->belongsToMany(
            PayrollComponent::class,
            'payroll_slip_template_deduction_components',
            'payroll_slip_template_id',
            'payroll_component_id'
        )->withPivot('order')
            ->withTimestamps();
    }

    public function applicableTos()
    {
        return $this->hasMany(PayrollSlipTemplateApplicableTo::class, 'payroll_slip_template_id');
    }
}
