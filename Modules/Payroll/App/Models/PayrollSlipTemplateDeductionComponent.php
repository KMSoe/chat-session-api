<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSlipTemplateDeductionComponent extends Model
{
    protected $fillable = [
        'payroll_slip_template_id',
        'payroll_component_id',
        'order',
    ];

    public function template()
    {
        return $this->belongsTo(PayrollSlipTemplate::class);
    }

    public function component()
    {
        return $this->belongsTo(PayrollComponent::class);
    }
}
