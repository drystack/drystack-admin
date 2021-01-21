<?php


namespace Drystack\Crud\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeCrudPage extends Command {
    protected $signature = 'drystack:crud:make {name} {--model=} {--view=}';

    protected $description = 'Setup a Drystack crud page';

    public function handle() {

        $namespace = config('livewire.class_namespace');
        $view_path = config('livewire.view_path');

        if (empty($namespace)) {
            $this->error("Livewire's class_namespace is not configured for your project. Please configure Livewire");
            return -1;
        }

        if (empty($view_path)) {
            $this->error("Livewire's view_path is not configured for your project. Please configure Livewire");
            return -1;
        }

        $view_path_laravel = substr($view_path, stripos($view_path, "views/") + 6);
        $view_prefix = str_replace('/', '.', '$view_path_laravel');

        $name = strtolower($this->argument('name'));
        $class = ucfirst($name);

        //$namespace .= "\\$class";

        $model = $this->option('model') ?? ucfirst($name);
        $view = $view_prefix . '.' . ($this->option('view') ?? $name);

        $page = file_get_contents(__DIR__ . '/../../stubs/Page.stub');
        $page = str_replace("{{namespace}}", $namespace, $page);
        $page = str_replace("{{name}}", $class, $page);
        $page = str_replace("{{title}}", $class, $page);
        $page = str_replace("{{model}}", $model, $page);
        $page = str_replace("{{view}}", $view, $page);

        $page_name = "$namespace\\$class"."Page";

        file_put_contents($this->getPath($page_name), $page);

        $view_page = file_get_contents(__DIR__ . '/../../stubs/page.blade.stub');
        $view_page = str_replace("{{name}}", $name, $view_page);

        file_put_contents($view_path . '/' . $name . "-page.blade.php" , $view_page);


        $datatable = file_get_contents(__DIR__ . '/../../stubs/Datatable.stub');
        $datatable = str_replace("{{name}}", $class, $datatable);
        $datatable = str_replace("{{model}}", $model, $datatable);
        $datatable = str_replace("{{namespace}}", $namespace, $datatable);

        $datatable_page_name = "$namespace\\$class"."Datatable";

        file_put_contents($this->getPath($datatable_page_name), $datatable);
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->laravel->getNamespace(), '', $name);

        return app_path(str_replace('\\', '/', $name).'.php') ;
    }
}
