<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Employee\App\Models\Employee;

class ReconnectUserToEmployee extends Command
{
    protected $signature = 'user:connectEmployee';
    protected $description = 'Reconnect users to employees based on matching email addresses.';

    public function handle()
    {
        $users = User::select('id', 'email', 'employee_id')->whereNull('deleted_at')->get();

        foreach($users as $user)
        {
            $employee = Employee::where('email', $user->email)->whereNull('deleted_at')->select('id')->first();
            $employeeId = $employee ? $employee->id : null;

            if($employeeId !== $user->employee_id)
            {
                $user->update(['employee_id' => $employeeId]);
                $this->info("User ID {$user->id} reconnected to Employee ID {$employeeId}");
            }
        }
    }
}