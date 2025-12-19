<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollOvertimeSummary extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payroll_overtime_summaries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payroll_id',
        'total_hours',
        'average_rate',
        'amount',
        'overtime_details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_hours'      => 'double',
        'average_rate'     => 'double',
        'amount'           => 'double',
        'overtime_details' => 'array', // Automatically cast JSON to PHP array
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Get the payroll that owns this overtime summary.
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
