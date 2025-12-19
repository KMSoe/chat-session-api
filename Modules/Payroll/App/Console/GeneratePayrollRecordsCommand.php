<?php
namespace Modules\Payroll\App\Console;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Payroll\App\Enums\PayFrequencyTypes;
use Modules\Payroll\App\Enums\PayrollStatusTypes;
use Modules\Payroll\App\Helpers\PayrollHelper;
use Modules\Payroll\App\Models\Payroll;
use Modules\Payroll\App\Models\PayrollPolicy;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GeneratePayrollRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payroll:generate-payrolls';

    /**
     * The console command description.
     */
    protected $description = 'This Command is used to generate payroll records in the start day of payroll ploicy starter date.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::first(1);

        $payroll_policies = PayrollPolicy::with(['employees:id,name,salary_currency_id,basic_salary'])
            ->where('pay_frequency', PayFrequencyTypes::MONTHLY->value)
            ->where('pay_cycle_start_date', Carbon::now()->day)
            ->get();

        foreach ($payroll_policies as $key => $payroll_policy) {
            $pay_cycle_data = PayrollHelper::getPayCycleStartAndEnd($payroll_policy->pay_cycle_start_date, Carbon::now());
            $start_date     = $pay_cycle_data['start_date'];
            $payroll_month  = $start_date->format('Y-m-d');

            $data = $payroll_policy->employees->map(function ($employee) use ($user, $payroll_month) {
                return [
                    'employee_id'      => $employee->id,
                    'currency_id'      => $employee->salary_currency_id ?? 0,
                    'payroll_month'    => $payroll_month,
                    'basic_salary'     => $employee->basic_salary,
                    'gross_salary'     => 0,
                    'total_deductions' => 0,
                    'net_pay'          => 0,
                    'status'           => PayrollStatusTypes::NOT_CALCULATED->value,
                    'updated_by'       => $user->id,
                ];
            });

            Payroll::insert($data);
        }
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
