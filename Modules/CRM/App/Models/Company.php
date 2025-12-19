<?php

namespace Modules\CRM\App\Models;

use App\Models\CustomValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Storage\App\Models\File;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\Currency;
use Nnjeim\World\Models\State;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'crm_companies';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_code',
        'logo_file_id',
        'name',
        'domain',
        'email',
        'phone_dial_code',
        'phone_number',
        'tax_id',
        'currency_id',
        'billing_country_id',
        'billing_state_id',
        'billing_district',
        'billing_zip_code',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_phone_dial_code',
        'billing_phone_number',
        'shipping_same_as_billing',
        'shipping_country_id',
        'shipping_state_id',
        'shipping_district',
        'shipping_zip_code',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_phone_dial_code',
        'shipping_phone_number',
        'status',
        'created_by',
        'updated_by',
    ];
    
    public function customValues()
    {
        return $this->hasMany(CustomValue::class, 'target_id')->whereHas('attribute.modules', function ($q) {
            $q->where('name', 'company');
        });
    }

    public function logo()
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'company_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function billingCountry()
    {
        return $this->belongsTo(Country::class, 'billing_country_id');
    }

    public function billingState()
    {
        return $this->belongsTo(State::class, 'billing_state_id');
    }

    public function shippingCountry()
    {
        return $this->belongsTo(Country::class, 'shipping_country_id');
    }

    public function shippingState()
    {
        return $this->belongsTo(State::class, 'shipping_state_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function comments()
    {
        return $this->hasMany(CompanyComment::class, 'company_id');
    }
}
