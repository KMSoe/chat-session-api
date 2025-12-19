<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomValue extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'custom_values';

    protected $fillable = [
        'attribute_id',
        'target_id',
        'target_value',
        'is_encrypted',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
