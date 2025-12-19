<?php
namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Employee\App\Models\Employee;
use Nnjeim\World\Models\Currency;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'recurring_invoice_id',
        'company_id',
        'contact_id',
        'invoice_number',
        'reference_number',
        'invoice_date',
        'currency_id',
        'payment_term_id',
        'due_date',
        'project_id',
        'sale_person_id',
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
        'invoice_status',
        'status',
        'taxation_level',
        'discount_level',
        'created_by',
        'updated_by',
        'balance_due',
        'last_payment_date',
        'expected_payment_date',
        'void_date',
        'write_off_date',
    ];

    protected $casts = [
        'invoice_date'          => 'date',
        'due_date'              => 'date',
        'last_payment_date'     => 'date',
        'expected_payment_date' => 'date',
        'void_date'             => 'date',
        'write_off_date'        => 'date',
    ];

    public function recurringInvoice()
    {
        return $this->belongsTo(RecurringInvoice::class, 'recurring_invoice_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function itemTemplate()
    {
        return $this->belongsTo(ItemTemplate::class, 'item_template_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function creditNoteApplications()
    {
        return $this->hasMany(CreditNoteInvoiceApplication::class, 'invoice_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function organizationSignatures()
    {
        return $this->hasMany(InvoiceSignature::class, 'invoice_id')->where('type', 'organization')->select('id', 'invoice_id', 'label', 'assigned_signer_id', 'notify_signer', 'signature_file_id', 'signed_at', 'type');
    }

    public function customerSignatures()
    {
        return $this->hasMany(InvoiceSignature::class, 'invoice_id')->where('type', 'customer')->select('id', 'invoice_id', 'label', 'signature_file_id', 'signed_at', 'type');
    }

    public function emailCommunications()
    {
        return $this->hasMany(InvoiceEmailCommunication::class, 'invoice_id');
    }

    public function files()
    {
        return $this->hasMany(InvoiceFile::class, 'invoice_id');
    }

    public function actionOwners()
    {
        return $this->hasMany(InvoiceActionOwner::class, 'invoice_id');
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

    public function payments()
    {
        return $this->hasMany(InvoiceReceivedPayment::class, 'invoice_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(InvoiceActivityLog::class, 'invoice_id');
    }

    public function comments()
    {
        return $this->hasMany(InvoiceComment::class, 'invoice_id');
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
        return $this->hasMany(InvoiceInvitationLink::class, 'invoice_id')->where('is_active', true);
    }
}
