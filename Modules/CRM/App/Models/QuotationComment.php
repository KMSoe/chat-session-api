<?php

namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\QuotationCommentFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationComment extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['quotation_id', 'commented_by', 'comment'];
    
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function commentBy()
    {
        return $this->belongsTo(User::class, 'commented_by');
    }
}
