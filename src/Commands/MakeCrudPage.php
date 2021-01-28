<?php


namespace Drystack\Admin\Commands;

use Drystack\Admin\Commands\Traits\HasLivewire;
use Drystack\Admin\Commands\Traits\MakeFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeCrudPage extends Command {
    use HasLivewire, MakeFiles;

    protected $signature = 'drystack:admin:crud {name} {--model=} {--view=}';

    protected $description = 'Setup a Drystack crud page';

    public function handle() {
        if ($this->checkLivewireConfigured() == -1) return -1;

        $name = strtolower($this->argument('name'));
        $class = ucfirst($name);

        $view_path_laravel = substr($this->view_path, stripos($this->view_path, "views/") + 6);
        $view_prefix = str_replace('/', '.', "$view_path_laravel/$name");

        $this->namespace = $this->namespace . "\\$class";
        $this->makeControllerAndViewFolders($this->namespace, $this->view_path . "/$name");

        $model = $this->option('model') ?? ucfirst($name);

        $this->makePages(["Index", "Create", "Read", "Update", "Delete"], $this->namespace, $class, $model, $view_prefix);
        $this->makeViews(["index", "create", "read", "update"], $name, $this->view_path . "/$name");

        $this->makeDatatable($class, $model, $this->namespace);

        $page_name = "$this->namespace\\$class";
        $this->addRoutes($name, $page_name, ["index", "create", "read", "update", "delete"]);
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->laravel->getNamespace(), '', $name);

        return app_path(str_replace('\\', '/', $name).'.php') ;
    }

    protected function getPathNoExtension($name)
    {
        $name = Str::replaceFirst($this->laravel->getNamespace(), '', $name);

        return app_path(str_replace('\\', '/', $name)) ;
    }

    protected function addRoutes(string $name, string $page_name, array $routes) {
        $file = file_get_contents(base_path('routes/drystack.php'));
        $file .= "\n";
        foreach ($routes as $route) {
            $action = ucfirst($route);
            $route = "$name.$route";
            if (str_contains($file, $route)) continue;
            $path = str_replace(".", "/", $route);
            $file .= "Route::get('$path', {$page_name}Page{$action}::class)->middleware(['auth'])->name('$route');\n";
        }
        file_put_contents(base_path('routes/drystack.php'), $file);
    }

    protected function makePages(array $actions, string $namespace, string $class, string $model, string $view) {
        foreach ($actions as $action) {
            $this->makePage($action, $namespace, $class, $model, $view);
        }
    }

    protected function makePage(string $action, string $namespace, string $class, string $model, string $view) {
        $page = file_get_contents(__DIR__ . "/../../stubs/crud/Page$action.stub");
        $page = str_replace("{{namespace}}", $namespace, $page);
        $page = str_replace("{{name}}", $class, $page);
        $page = str_replace("{{title}}", $class, $page);
        $page = str_replace("{{model}}", $model, $page);
        $page = str_replace("{{prefix}}", $view, $page);
        $page = str_replace("{{view}}", strtolower($class), $page);

        $page_name = "$namespace\\$class";

        file_put_contents($this->getPath($page_name . "Page$action"), $page);
    }

    protected function makeViews(array $actions, string $name, string $path) {
        foreach ($actions as $action) {
            $this->makeView($action, $name, $path);
        }
    }

    protected function makeView(string $action, string $name, string $path) {
        $view_page = file_get_contents(__DIR__ . "/../../stubs/crud/page-$action.blade.stub");
        $view_page = str_replace("{{name}}", $name, $view_page);

        file_put_contents($path . '/' . $name . "-page-$action.blade.php" , $view_page);
    }

    protected function makeDatatable(string $class, string $model, string $namespace) {
        $datatable = file_get_contents(__DIR__ . '/../../stubs/crud/Datatable.stub');
        $datatable = str_replace("{{name}}", $class, $datatable);
        $datatable = str_replace("{{model}}", $model, $datatable);
        $datatable = str_replace("{{namespace}}", $namespace, $datatable);

        $datatable_page_name = "$namespace\\$model"."Datatable";

        file_put_contents($this->getPath($datatable_page_name), $datatable);
    }
}
