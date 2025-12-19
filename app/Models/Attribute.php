<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'attributes';

    protected $fillable = [
        'name',
        'type',
        'is_required',
        'status',
        'options',
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class)->withTimestamps();
    }

    public function customValues()
    {
        return $this->hasMany(CustomValue::class, 'attribute_id');
    }

    public function attributeSets()
    {
        return $this->belongsToMany(AttributeSet::class, 'attribute_set_attributes')
                    ->withPivot('sort')
                    ->withTimestamps()
                    ->orderBy('sort');
    }
}
