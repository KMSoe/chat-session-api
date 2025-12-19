<?php

namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\factories\RecurringInvoiceFactory;
use Modules\Employee\App\Models\Employee;
use Nnjeim\World\Models\Currency;

class RecurringInvoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'contact_id',
        'recurring_level',
        'start_date',
        'end_date',
        'recurring_interval',
        'recurrence_frequency',
        'reference_number',
        'currency_id',
        'payment_term_id',
        'project_id',
        'sale_person_id',
        'preference',
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
        'taxation_level',
        'discount_level',
        'created_by',
        'updated_by',
        'status',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

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
        return $this->hasMany(RecurringInvoiceItem::class, 'recurring_invoice_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function organizationSignatures()
    {
        return $this->hasMany(RecurringInvoiceSignature::class, 'recurring_invoice_id')->where('type', 'organization')->select('id', 'recurring_invoice_id', 'label', 'assigned_signer_id', 'notify_signer', 'type');
    }

    public function customerSignatures()
    {
        return $this->hasMany(RecurringInvoiceSignature::class, 'recurring_invoice_id')->where('type', 'customer')->select('id', 'recurring_invoice_id', 'label', 'type');
    }

    public function emailCommunications()
    {
        return $this->hasMany(RecurringInvoiceEmailCommunication::class, 'recurring_invoice_id');
    }

    public function files()
    {
        return $this->hasMany(RecurringInvoiceFile::class, 'recurring_invoice_id');
    }

    public function salePerson()
    {
        return $this->belongsTo(Employee::class, 'sale_person_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs()
    {
        return $this->hasMany(RecurringInvoiceLog::class, 'recurring_invoice_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'recurring_invoice_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(RecurringInvoiceActivityLog::class, 'recurring_invoice_id');
    }
}
