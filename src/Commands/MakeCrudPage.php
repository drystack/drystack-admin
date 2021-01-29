<?php


namespace Drystack\Admin\Commands;

use Drystack\Admin\Commands\Traits\HasLivewire;
use Drystack\Admin\Commands\Traits\MakeFiles;
use Drystack\Admin\Models\Ability;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeCrudPage extends Command {
    use HasLivewire, MakeFiles;

    protected $signature = 'drystack:admin:crud {name} {--model=} {--view=}';

    protected $description = 'Setup a Drystack crud page';

    public function handle() {
        if ($this->checkLivewireConfigured() == -1) return -1;

        $name = strtolower($this->argument('name'));
        $model_name = $this->option('model') ?? "\\App\\Models\\" . ucfirst($name);

        $this->makeCrudPage($name, $model_name);

        $abilities = [];
        foreach (["create", "read", "update", "delete"] as $ability) {
            $abilities[] = [
                'name' => $ability,
                'entity' => substr($model_name, 1)
            ];
        }
        DB::table('prm_abilities')->insert($abilities);
        if (file_exists($this->livewire_view_path . "/permission")) return 0;
        $this->makeCrudPage("role", "\\Drystack\\Admin\\Models\\Role");
    }


    protected function makeCrudPage(string $name, string $model_name) {
        $crud_name = ucfirst($name);
        $view_path_laravel = substr($this->livewire_view_path, stripos($this->livewire_view_path, "views/") + 6);
        $view_prefix = str_replace('/', '.', "$view_path_laravel/$name");

        $livewire_namespace = $this->livewire_namespace . "\\$crud_name";
        $livewire_view_path = $this->livewire_view_path . "/$name";
        $this->makeControllerAndViewFolders($livewire_namespace, $livewire_view_path);

        $this->makePages(["Index", "Create", "Read", "Update", "Delete"], $livewire_namespace, $crud_name, $model_name, $view_prefix);
        $this->makeViews(["index", "create", "read", "update"], $name, $livewire_view_path);

        $this->makeDatatable($crud_name, $model_name, $livewire_namespace);

        $page_name = "$livewire_namespace\\$crud_name";
        $this->addRoutes($name, $page_name, ["index" => '', "create" => '', "read" => '/{id}', "update" => '/{id}', "delete" => '/{id}']);
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
        foreach ($routes as $route => $parameters) {
            $action = ucfirst($route);
            $route = "$name.$route";
            if (str_contains($file, $route)) continue;
            $path = str_replace(".", "/", $route) . $parameters;
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

        $datatable_page_name = "$namespace\\$class"."Datatable";

        file_put_contents($this->getPath($datatable_page_name), $datatable);
    }
}
