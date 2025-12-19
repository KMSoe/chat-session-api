<?php
namespace App\Models;

use App\Filters\Filterable;
use Spatie\Permission\Models\Permission;

class CustomPermission extends Permission
{
    use Filterable;

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'module_permissions');
    }
}
