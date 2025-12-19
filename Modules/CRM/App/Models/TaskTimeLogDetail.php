<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\TaskTimeLogDetailFactory;

class TaskTimeLogDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'task_time_log_id',
        'start_time',
        'end_time',
        'duration_minutes',
    ];
    
    public function taskTimeLog()
    {
        return $this->belongsTo(TaskTimeLog::class);
    }
}
