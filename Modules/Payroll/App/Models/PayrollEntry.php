<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'type',
        'payroll_component_id', // default = 0
        'name',
        'affects_net_pay', // boolean
        'taxable',
        'amount',
    ];

    public function getAffectsNetPayAttribute($value): bool
    {
        return (bool) $value;
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    public function component()
    {
        return $this->belongsTo(PayrollComponent::class, 'payroll_component_id');
    }
}
