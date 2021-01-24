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
        $view_prefix = str_replace('/', '.', $view_path_laravel);

        $name = strtolower($this->argument('name'));
        $class = ucfirst($name);

        //$namespace .= "\\$class";

        $model = $this->option('model') ?? ucfirst($name);
        $view = $view_prefix . '.' . ($this->option('view') ?? $name);

        $this->makePage("Index", $namespace, $class, $model, $view);
        $this->makePage("Create", $namespace, $class, $model, $view);
        $this->makePage("Read", $namespace, $class, $model, $view);
        $this->makePage("Update", $namespace, $class, $model, $view);
        $this->makePage("Delete", $namespace, $class, $model, $view);

        $this->makeView("index", $name, $view_path);
        $this->makeView("create", $name, $view_path);
        $this->makeView("read", $name, $view_path);
        $this->makeView("update", $name, $view_path);

        $this->makeDatatable($class, $model, $namespace);

        $page_name = "$namespace\\$class";
        $routes = file_get_contents(base_path('routes/web.php'));
        $routes = $this->addRoutes($routes, $page_name, [
            "$name.index",
            "$name.create",
            "$name.read",
            "$name.update",
            "$name.delete",
        ]);
        file_put_contents(base_path('routes/web.php'), $routes);

    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->laravel->getNamespace(), '', $name);

        return app_path(str_replace('\\', '/', $name).'.php') ;
    }

    protected function addRoutes(string $file, string $page_name, array $routes): string {
        $file .= "\n";
        foreach ($routes as $route) {
            if (str_contains($file, $route)) continue;
            $parts = explode(".", $route);
            $action = ucfirst(end($parts));
            $path = str_replace(".", "/", $route);
            $file .= "Route::get('$path', {$page_name}Page{$action}::class)->name('$route');\n";
        }
        return $file;
    }

    protected function makePage(string $action, string $namespace, string $class, string $model, string $view) {
        $page = file_get_contents(__DIR__ . "/../../stubs/Page$action.stub");
        $page = str_replace("{{namespace}}", $namespace, $page);
        $page = str_replace("{{name}}", $class, $page);
        $page = str_replace("{{title}}", $class, $page);
        $page = str_replace("{{model}}", $model, $page);
        $page = str_replace("{{view}}", $view, $page);

        $page_name = "$namespace\\$class";

        file_put_contents($this->getPath($page_name . "Page$action"), $page);
    }

    protected function makeView(string $action, string $name, string $path) {
        $view_page = file_get_contents(__DIR__ . "/../../stubs/page-$action.blade.stub");
        $view_page = str_replace("{{name}}", $name, $view_page);

        file_put_contents($path . '/' . $name . "-page-$action.blade.php" , $view_page);
    }

    protected function makeDatatable(string $class, string $model, string $namespace) {
        $datatable = file_get_contents(__DIR__ . '/../../stubs/Datatable.stub');
        $datatable = str_replace("{{name}}", $class, $datatable);
        $datatable = str_replace("{{model}}", $model, $datatable);
        $datatable = str_replace("{{namespace}}", $namespace, $datatable);

        $datatable_page_name = "$namespace\\$class"."Datatable";

        file_put_contents($this->getPath($datatable_page_name), $datatable);
    }

}
