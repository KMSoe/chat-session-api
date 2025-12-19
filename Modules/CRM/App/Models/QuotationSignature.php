<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\QuotationSignatureFactory;
use Modules\Employee\App\Models\Employee;
use Modules\Storage\App\Models\File;

class QuotationSignature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'quotation_id',
        'type',
        'label',
        'assigned_signer_id',
        'signed_at',
        'notify_signer',
        'signature_file_id',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function assignedSigner()
    {
        return $this->belongsTo(Employee::class, 'assigned_signer_id');
    }

    public function signatureFile()
    {
        return $this->belongsTo(File::class, 'signature_file_id');
    }

    public function getCanSignAttribute()
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        $employee = Employee::where('user_id', $user->id)->first();

        if ($this->type == 'organization') {
            if ($this->assigned_signer_id) {
                return $employee && $employee->id == $this->assigned_signer_id;
            }
            return true;
        }

        if ($this->type === 'customer') {
            return true;
        }

        return false;
    }
}
