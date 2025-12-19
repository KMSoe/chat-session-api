<?php

namespace Modules\CRM\App\Models;

use App\Models\CustomValue;
use App\Models\User;
use App\Trait\HasApplicableScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\factories\TaskFactory;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasApplicableScope;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'task_code',
        'title',
        'priority',
        'project_id',
        'status_id',
        'description',
        'due_date',
        'sort_order',
        'is_active',
        'completed_at',
        'has_reminder',
        'notification_reminder',
        'notification_custom_minutes',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function status()
    {
        return $this->belongsTo(ProjectTaskStatus::class, 'status_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function customValues()
    {
        return $this->hasMany(CustomValue::class, 'target_id')->whereHas('attribute.modules', function ($q) {
            $q->where('name', 'task');
        });
    }

    public function activityLogs()
    {
        return $this->hasMany(TaskActivityLog::class, 'task_id');
    }

    public function applicableTos()
    {
        return $this->hasMany(TaskApplicableTo::class, 'task_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_id');
    }

    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class, 'task_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
