<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleMakeClassCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make-class {name} {--methods=} {module}';

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
        $moduleName = $this->argument('module');
        $name       = $this->argument('name');
        $methods = $this->option('methods') ?? '';
        // $path = app_path(path: $name . '.php');

        // if (file_exists($path)) {
        //     $this->error("Class already exists at {$path}");
        //     return;
        // }

        $namespace = "Modules\\{$moduleName}";
        if (str_contains($name, '/')) {
            $segments = explode('/', $name);
            $class    = array_pop($segments);
            $namespace .= '\\' . implode('\\', $segments);
            $dir = app_path(implode('/', $segments));
            if (! file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        } else {
            $class = $name;
        }

        $methodStubs = '';
        if (!empty($methods)) {
            $methodList = array_map('trim', explode(',', $methods));
            foreach ($methodList as $method) {
                $methodStubs .= <<<PHP

    public function {$method}()
    {
        //
    }

PHP;
            }
        }

        $stub = <<<PHP
<?php

namespace {$namespace};

class {$class}
{
    {$methodStubs}
}

PHP;

        File::ensureDirectoryExists(dirname("Modules/$moduleName/$name.php"));
        file_put_contents("Modules/$moduleName/$name.php", $stub);
    }
}
