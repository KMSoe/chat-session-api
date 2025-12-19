<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'modules';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_module_id',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_module_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_module_id');
    }

    public function childrenRecursive()
    {
        return $this->hasMany(self::class, 'parent_module_id')->with('childrenRecursive');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class)->where('status', 1)->withTimestamps();
    }

    public function allPermissions()
    {
        return $this->belongsToMany(CustomPermission::class, 'module_permissions', 'module_id', 'permission_id');
    }

    public function assignedPermissions()
    {
        return $this->belongsToMany(CustomPermission::class, 'role_module_permissions', 'module_id', 'permission_id')
            ->withPivot('role_id');
    }

    public function attributeSets()
    {
        return $this->hasMany(AttributeSet::class, 'module_id');
    }
}
