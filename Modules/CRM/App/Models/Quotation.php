<?php

namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Employee\App\Models\Employee;
use Nnjeim\World\Models\Currency;

class Quotation extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'contact_id',
        'quotation_number',
        'reference_number',
        'quotation_date',
        'expiry_date',
        'currency_id',
        'project_id',
        'item_template_id',
        'customer_note',
        'terms_and_conditions',
        'subtotal',
        'tax_id',
        'tax_amount',
        'discount_type',
        'discount_value',
        'grand_total',
        'enable_organization_signature',
        'enable_customer_signature',
        'quotation_status',
        'status',
        'accepted_at',
        'declined_at',
        'created_by',
        'updated_by',
        'taxation_level',
        'sale_person_id',
        'discount_level',
        'decline_reason',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'expiry_date' => 'date',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function itemTemplate()
    {
        return $this->belongsTo(ItemTemplate::class, 'item_template_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function organizationSignatures()
    {
        return $this->hasMany(QuotationSignature::class, 'quotation_id')->where('type', 'organization')->select('id', 'quotation_id', 'label', 'assigned_signer_id', 'notify_signer', 'signature_file_id', 'signed_at', 'type');
    }

    public function customerSignatures()
    {
        return $this->hasMany(QuotationSignature::class, 'quotation_id')->where('type', 'customer')->select('id', 'quotation_id', 'label', 'signature_file_id', 'signed_at', 'type');
    }

    public function emailCommunications()
    {
        return $this->hasMany(QuotationEmailCommunication::class, 'quotation_id');
    }

    public function files()
    {
        return $this->hasMany(QuotationFile::class, 'quotation_id');
    }

    public function actionOwners()
    {
        return $this->hasMany(QuotationActionOwner::class, 'quotation_id');
    }

    public function salePerson()
    {
        return $this->belongsTo(Employee::class, 'sale_person_id');
    }

    public function getCanApproveAttribute()
    {
        $userId = auth()->id();

        $userId = Employee::where('user_id', $userId)->value('id');

        $pendingLevel = $this->actionOwners()->select('level')->groupBy('level')->havingRaw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) = 0')
            ->orderBy('level')->value('level');

        if (! $pendingLevel) {
            return false;
        }

        return $this->actionOwners()->where('level', $pendingLevel)->where('action_owner_id', $userId)->where('status', 'pending')->exists();
    }

    public function getApprovalStatusAttribute()
    {
        $owners = $this->actionOwners()->orderBy('level')->get()->groupBy('level');

        foreach ($owners as $level => $approvers) {
            if ($approvers->contains('status', 'rejected')) {
                return 'rejected';
            }

            if ($approvers->contains('status', 'pending') && ! $approvers->contains('status', 'approved')) {
                if ($level === 1) {
                    return 'not started';
                }
                return "level " . ($level - 1) . " approved";
            }

            if ($approvers->contains('status', 'approved')) {
                continue; // move to next level
            }
        }

        return 'approved';
    }

    public function activityLogs()
    {
        return $this->hasMany(QuotationActivityLog::class, 'quotation_id');
    }

    public function comments()
    {
        return $this->hasMany(QuotationComment::class, 'quotation_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function invitationLinks()
    {
        return $this->hasMany(QuotationInvitationLink::class, 'quotation_id')->where('is_active', true);
    }
}