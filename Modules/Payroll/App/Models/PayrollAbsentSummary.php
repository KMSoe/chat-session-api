<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAbsentSummary extends Model
{
    protected $table = 'payroll_absent_summaries';

    protected $fillable = [
        'payroll_id',
        'days',
        'daily_rate',
        'amount',
        'absent_details',
    ];

    protected $casts = [
        'days'           => 'integer',
        'daily_rate'     => 'double',
        'amount'         => 'double',
        'absent_details' => 'array', // Auto-cast JSON to array
    ];

    /**
     * Get the payroll that owns this absence summary.
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
