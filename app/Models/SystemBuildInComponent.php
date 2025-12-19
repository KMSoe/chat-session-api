<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemBuildInComponent extends Model
{
    use HasFactory;

    protected $table = 'system_build_in_components';

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'is_active',
    ];

}
