<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Storage\App\Models\File;
use Spatie\Activitylog\Traits\LogsActivity;

class PayrollSlip extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payroll_id',
        'payslip_template_id',
        'file_id',
        'status',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('payroll_slip');
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function template()
    {
        return $this->belongsTo(PayrollSlipTemplate::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
