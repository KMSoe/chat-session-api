<?php

namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Payroll\Database\factories\AverageDailyWageApplicableToFactory;

class AverageDailyWageApplicableTo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'average_daily_wage_id',
        'scope',
        'target_id',
        'include_children',
    ];
    
    public function averageDailyWage()
    {
        return $this->belongsTo(AverageDailyWage::class);
    }
}
