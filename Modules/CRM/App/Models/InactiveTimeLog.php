<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\InactiveTimeLogFactory;
use Modules\Employee\App\Models\Employee;

class InactiveTimeLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'date',
        'total_working_minutes',
        'active_minutes',
        'inactive_minutes',
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
