<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\QuotationInvitationLinkFactory;

class QuotationInvitationLink extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'quotation_id',
        'invitation_token',
        'expires_at',
        'is_active',
        'invitation_link',
    ];
    
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }
}
