<?php
namespace Modules\Payroll\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class TaxForm extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'form_type',
        'description',
        'period_type', // 'single', 'range'
        'sort_order',
        'is_enable',
    ];

    protected $appends = ['periods'];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('tax_form');
    }

    public function getIsEnableAttribute($value)
    {
        return $value ? true : false;
    }

    public function getPeriodsAttribute()
    {

        if ($this->period_type == 'range') {
            $now         = now(); // Carbon instance
            $currentYear = $now->month >= 4 ? $now->year : $now->year - 1;

            for ($i = 0; $i < 10; $i++) {
                $startYear = $currentYear - $i;
                $endYear   = $startYear + 1;

                $data[] = [
                    'start_date' => Carbon::create($startYear, 4, 1)->toDateString(), // YYYY-04-01
                    'end_date'   => Carbon::create($endYear, 3, 31)->toDateString(),  // (YYYY+1)-03-31
                ];
            }

            return $data;
        } else {
            $data = [];

            $now = now()->addMonths(2); // current + 2 months
            $end = now()->subYears(2);

            while ($now->greaterThanOrEqualTo($end)) {
                $data[] = [
                    'date' => $now->format('Y-m'),
                ]; // YYYY-MM
                $now->subMonth();
            }

            return $data;
        }
    }

    public function incomeCategories()
    {
        return $this->hasMany(TaxFormIncomeCategory::class, 'tax_form_id');
    }
}
