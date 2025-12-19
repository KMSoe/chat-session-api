<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSlipTemplateApplicableTo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payroll_slip_template_id',
        'scope',
        'target_id',
        'include_children',
    ];

    public function payrollSlipTemplate()
    {
        return $this->belongsTo(PayrollSlipTemplate::class, 'payroll_slip_template_id');
    }
}
