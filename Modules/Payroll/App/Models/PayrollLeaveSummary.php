<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollLeaveSummary extends Model
{
    protected $fillable = [
        'payroll_id',
        'total_carried_forward',
        'total_entitled',
        'total_taken',
        'total_adjusted',
        'total_balance',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }
}
