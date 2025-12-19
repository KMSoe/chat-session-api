<?php
namespace Modules\Payroll\App\Models;

use App\Trait\HasApplicableScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class PayrollComponent extends Model
{
    use HasFactory, SoftDeletes, HasApplicableScope, LogsActivity;

    protected $table = 'payroll_components';

    protected $fillable = [
        'code',
        'name',
        'is_system_default',
        'payroll_component_category_id',
        'component_type',
        'component_scope_mode',
        'calculation_type',
        'amount',
        'rate',
        'system_build_in_payroll_component',
        'formula',
        'taxable',
        'affects_net_pay',
        'recurring',
        'effected_month',
        'effected_months',
        'employment_types',
        'remarks',
        'is_active',
    ];

    protected $casts = [
        'is_system_default' => 'boolean',
        'taxable'           => 'boolean',
        'affects_net_pay'   => 'boolean',
        'recurring'         => 'boolean',
        'is_active'         => 'boolean',
        'employment_types'  => 'array',
        'amount'            => 'double',
        'rate'              => 'double',
        'effected_months'   => 'array',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('payroll_component');
    }

    public function category()
    {
        return $this->belongsTo(PayrollComponentCategory::class, 'payroll_component_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function applicableTos()
    {
        return $this->hasMany(PayrollComponentApplicableTo::class, 'payroll_component_id');
    }
}
