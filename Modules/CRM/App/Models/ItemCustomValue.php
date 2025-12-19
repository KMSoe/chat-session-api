<?php
namespace Modules\CRM\App\Models;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCustomValue extends Model
{
    use HasFactory;

    protected $table = 'item_custom_values';

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
