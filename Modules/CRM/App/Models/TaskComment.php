<?php

namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\factories\TaskCommentFactory;

class TaskComment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['task_id', 'commented_by', 'comment'];
    
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function commentBy()
    {
        return $this->belongsTo(User::class, 'commented_by');
    }
}
