<?php
namespace App\Http\Services;

use App\Repositories\RoleRepo;

class RoleService
{
    private RoleRepo $roleRepo;

    public function __construct(RoleRepo $roleRepo)
    {
        $this->roleRepo = $roleRepo;
    }

    
}
