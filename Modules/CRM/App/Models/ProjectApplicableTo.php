<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\ProjectApplicableToFactory;

class ProjectApplicableTo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_id',
        'scope',
        'target_id',
        'include_children',
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
