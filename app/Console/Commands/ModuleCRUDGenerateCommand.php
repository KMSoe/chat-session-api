<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Nwidart\Modules\Facades\Module;

class ModuleCRUDGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:files
                            {--module= : The name of the module}
                            {--model= : The name of the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $moduleName = $this->option('module');
        $modelName  = $this->option('model');

        if (! Module::has($moduleName)) {
            $this->error("Module '{$moduleName}' does not exist.");
            return 1;
        }

        Artisan::call("module:make-controller", [
            "controller" => "Api\\{$modelName}Controller",
            '--api'      => true,
            "module"     => $moduleName,
        ]);

        Artisan::call("module:make-request", [
            "name" => "Store{$modelName}Request",
            "module"  => $moduleName,
        ]);

        Artisan::call("module:make-request", [
            "name" => "Update{$modelName}Request",
            "module"  => $moduleName,
        ]);

        Artisan::call("module:make-resource", [
            "name" => "{$modelName}Resource",
            "module"  => $moduleName,
        ]);

        Artisan::call('module:make-class', [
            'name'      => "App/Services/{$modelName}Service",
            'module'    => $moduleName,
            '--methods' => 'findAll,findById,store,update,delete',
        ]);

        Artisan::call('module:make-class', [
            'name'      => "App/Repositories/{$modelName}Repository",
            'module'    => $moduleName,
            '--methods' => 'findAll,findById,store,update,delete',
        ]);

        $this->info("Done");
    }
}
