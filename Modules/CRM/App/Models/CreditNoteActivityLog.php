<?php

namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\factories\CreditNoteActivityLogFactory;

class CreditNoteActivityLog extends Model
{
   use HasFactory, SoftDeletes;

    protected $fillable = [
        'credit_note_id',
        'description',
        'action_by',
    ];
    
    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
