<?php

namespace Modules\CRM\App\Models;

use App\Models\CustomValue;
use App\Models\User;
use App\Trait\HasApplicableScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Calendar\App\Models\Event;
use Modules\Employee\App\Models\Department;
use Modules\Employee\App\Models\Designation;
use Modules\Employee\App\Models\Employee;
use Modules\Employee\App\Models\Group;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasApplicableScope;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_code',
        'name',
        'description',
        'owner_id',
        'project_status_id',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
        'updated_by',
        'visibility',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    public function owner()
    {
        return $this->belongsTo(Employee::class, 'owner_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function projectStatuses()
    {
        return $this->hasMany(ProjectStatus::class, 'project_id');
    }

    public function taskStatuses()
    {
        return $this->hasMany(ProjectTaskStatus::class, 'project_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function customValues()
    {
        return $this->hasMany(CustomValue::class, 'target_id')->whereHas('attribute.modules', function ($q) {
            $q->where('name', 'project');
        });
    }

    public function applicableTos()
    {
        return $this->hasMany(ProjectApplicableTo::class, 'project_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'project_id');
    }

    /**
     * Calculate project completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->relationLoaded('taskStatuses')) {
            $totalTasks = $this->taskStatuses->sum(function ($status) {
                return $status->relationLoaded('tasks') ? $status->tasks->count() : 0;
            });
            
            if ($totalTasks === 0) {
                return 0;
            }

            $completedTasks = $this->taskStatuses
                ->where('category', 'complete')
                ->sum(function ($status) {
                    return $status->relationLoaded('tasks') ? $status->tasks->count() : 0;
                });

            return round(($completedTasks / $totalTasks) * 100, 2);
        }

        // Fallback to query if relationships not loaded
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()
            ->whereHas('status', function ($query) {
                $query->where('category', 'complete');
            })
            ->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }
}
