<?php

namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\factories\CompanyCommentFactory;

class CompanyComment extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['company_id', 'commented_by', 'comment'];
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function commentBy()
    {
        return $this->belongsTo(User::class, 'commented_by');
    }
}
