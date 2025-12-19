<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollUnpaidLeaveSummary extends Model
{
    protected $table = 'payroll_unpaid_leave_summaries';

    protected $fillable = [
        'payroll_id',
        'days',
        'daily_rate',
        'amount',
        'leave_details',
    ];

    protected $casts = [
        'days'          => 'integer',
        'daily_rate'    => 'double',
        'amount'        => 'double',
        'leave_details' => 'array', // Auto-cast JSON to array
    ];

    /**
     * Get the payroll that owns this unpaid leave summary.
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
