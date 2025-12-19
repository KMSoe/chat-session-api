<?php

namespace Modules\Payroll\App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Employee\App\Models\Employee;
use Modules\Employee\App\Policies\EmployeePolicy;
use Modules\Payroll\App\Models\PayrollComponent;
use Modules\Payroll\App\Policies\PayrollComponentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the module.
     *
     * @var array
     */
    protected $policies = [
        PayrollComponent::class => PayrollComponentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
