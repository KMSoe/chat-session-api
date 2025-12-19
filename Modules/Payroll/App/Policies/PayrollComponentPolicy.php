<?php

namespace Modules\Payroll\App\Policies;

use App\Enums\ModuleNames;
use App\Enums\PermissionTypes;
use App\Models\User;
use App\Trait\HasRoleModulePermission;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Payroll\App\Models\PayrollComponent;

class PayrollComponentPolicy
{
    use HandlesAuthorization, HasRoleModulePermission;

   /**
     * View payroll components list
     */
    public function viewAny(User $user): bool
    {
        return $this->canAccess(ModuleNames::PAYROLL_COMPONENT->value, PermissionTypes::VIEW->value);
    }

    /**
     * View a single payroll component
     */
    public function view(User $user): bool
    {
        return $this->canAccess(ModuleNames::PAYROLL_COMPONENT->value, PermissionTypes::VIEW->value);
    }

    /**
     * Create a payroll component
     */
    public function create(User $user): bool
    {
        return $this->canAccess(ModuleNames::PAYROLL_COMPONENT->value, PermissionTypes::CREATE->value);
    }

    /**
     * Update payroll component
     */
    public function update(User $user): bool
    {
        return $this->canAccess(ModuleNames::PAYROLL_COMPONENT->value, PermissionTypes::EDIT->value);
    }

    /**
     * Delete payroll component
     */
    public function delete(User $user): bool
    {
        return $this->canAccess(ModuleNames::PAYROLL_COMPONENT->value, PermissionTypes::DELETE->value);
    }
}
