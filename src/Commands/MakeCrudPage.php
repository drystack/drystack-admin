<?php


namespace Drystack\Admin\Commands;

use Drystack\Admin\Commands\Traits\HasLivewire;
use Drystack\Admin\Commands\Traits\MakeFiles;
use Drystack\Admin\Models\Ability;
use Drystack\Admin\Models\Role;
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
        $model_name = $this->option('model') ?? "\\App\\Models\\" . $this->argument('name');

        $this->makeCrudPage($this->argument('name'), $model_name);

        $this->addAbilitiesAndRole($model_name);

        $this->makePolicy($this->argument('name'), $model_name);
        if (file_exists($this->livewire_view_path . "/permission")) return 0;
        $this->makeCrudPage("role", "\\Drystack\\Admin\\Models\\Role");
    }

    protected function addAbilitiesAndRole(string $model_name) {
        $abilities = [];
        foreach (["create", "read", "update", "delete"] as $ability) {
            $abilities[] = [
                'name' => $ability,
                'entity' => substr($model_name, 1)
            ];
        }
        DB::table('prm_abilities')->insert($abilities);
        $role = Role::find(1);
        if ($role == null) {
            $role->name = "Administrator";
            $role->save();
        }

        $role_abilities = [];
        $abilities_to_add = Ability::where('entity', substr($model_name, 1))->pluck('id');
        foreach ($abilities_to_add as $ability) {
            $role_abilities[] = [
                'role_id' =>  $role->id,
                'ability_id' => $ability
            ];
        }

        $role->rolesAbilities()->insert($role_abilities);
    }

    protected function makeCrudPage(string $name, string $model_name) {
        $crud_name = ucfirst($name);
        $view_name = strtolower($name);
        $view_path_laravel = substr($this->livewire_view_path, stripos($this->livewire_view_path, "views/") + 6);
        $view_prefix = str_replace('/', '.', "$view_path_laravel/$view_name");

        $livewire_namespace = $this->livewire_namespace . "\\$crud_name";
        $livewire_view_path = $this->livewire_view_path . "/$view_name";
        $this->makeControllerAndViewFolders($livewire_namespace, $livewire_view_path);

        $this->makePages(["Index", "Create", "Read", "Update", "Delete"], $livewire_namespace, $crud_name, $model_name, $view_prefix);
        $this->makeViews(["index", "create", "read", "update"], $view_name, $livewire_view_path);

        $this->makeDatatable($crud_name, $model_name, $livewire_namespace);

        $page_name = "$livewire_namespace\\$crud_name";
        $this->addRoutes($view_name, $page_name, ["index" => '', "create" => '', "read" => '/{id}', "update" => '/{id}', "delete" => '/{id}']);
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
        $subdir = strtolower($class);
        $subdir = file_exists(__DIR__ . "/../../stubs/crud/$subdir/Page$action.stub") ? "$subdir/" : "";
        $page = file_get_contents(__DIR__ . "/../../stubs/crud/{$subdir}Page$action.stub");
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
        $subdir = file_exists(__DIR__ . "/../../stubs/crud/$name/page-$action.blade.stub") ? "$name/" : "";
        $view_page = file_get_contents(__DIR__ . "/../../stubs/crud/{$subdir}page-$action.blade.stub");
        $view_page = str_replace("{{name}}", $name, $view_page);

        file_put_contents($path . '/' . $name . "-page-$action.blade.php" , $view_page);
    }

    protected function makeDatatable(string $class, string $model, string $namespace) {
        $subdir = strtolower($class);
        $subdir = file_exists(__DIR__ . "/../../stubs/crud/$subdir/Datatable.stub") ? "$subdir/" : "";
        $datatable = file_get_contents(__DIR__ . "/../../stubs/crud/{$subdir}Datatable.stub");
        $datatable = str_replace("{{name}}", $class, $datatable);
        $datatable = str_replace("{{model}}", $model, $datatable);
        $datatable = str_replace("{{namespace}}", $namespace, $datatable);

        $datatable_page_name = "$namespace\\$class"."Datatable";

        file_put_contents($this->getPath($datatable_page_name), $datatable);
    }

    protected function makePolicy(string $name, string $model_name) {
        $policy_name = $model_name;

        if (!file_exists(app_path('Policies'))) {
            mkdir(app_path('Policies'), 0755);
        }

        $use_model = str_contains($model_name, "User") ? "" : "use $model_name;";

        $policy_stub = file_get_contents(__DIR__ . "/../../stubs/Policy.stub");
        $policy_stub = str_replace("{{name}}", $policy_name, $policy_stub);
        $policy_stub = str_replace("{{use_model}}", $use_model, $policy_stub);

        file_put_contents(app_path("Policies/{$policy_name}Policy.php"), $policy_stub);

    }
}
