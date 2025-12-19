<?php
namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNoteComment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['credit_note_id', 'commented_by', 'comment'];

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function commentBy()
    {
        return $this->belongsTo(User::class, 'commented_by');
    }
}
