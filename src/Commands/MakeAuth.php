<?php


namespace Drystack\Admin\Commands;

use Drystack\Admin\Commands\Traits\HasLivewire;
use Drystack\Admin\Commands\Traits\MakeFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeAuth extends Command {
    use HasLivewire, MakeFiles;

    protected $signature = 'drystack:admin:auth';

    protected $description = 'Setup Drystack admin scaffolding';

    public function handle() {
        if ($this->checkLivewireConfigured() == -1) return -1;
        
        $dash_namespace = $this->namespace . "\\Dashboard";
        $dash_view_path = $this->view_path . "/dashboard";
        $this->view_path = $this->view_path . "/auth";
        $this->namespace = $this->namespace . "\\Auth";
        $this->makeControllerAndViewFolders($this->namespace, $this->view_path);
        $this->makeControllerAndViewFolders($dash_namespace, $dash_view_path);
        
        copy(__DIR__ . '/../../stubs/auth/auth.stub', $this->view_path . "/auth.blade.php");
        copy(__DIR__ . '/../../stubs/auth/forgot-password.stub', $this->view_path . "/forgot-password.blade.php");
        copy(__DIR__ . '/../../stubs/auth/reset-password.stub', $this->view_path . "/reset-password.blade.php");
        copy(__DIR__ . '/../../stubs/dashboard/dashboard.stub', $dash_view_path . "/dashboard.blade.php");

        $this->makePages(["LoginPage", "ForgotPasswordPage", "ResetPasswordPage"], $this->namespace, "Auth");
        $this->makePage("DashboardPage", $dash_namespace, "Dashboard", 'dashboard');
        $this->makeRoutes([
            "login" => ['method' => 'get', 'protected' => false],
            "forgot-password" => ['method' => 'get', 'protected' => false],
            "reset-password" => ['method' => 'get', 'protected' => false],
            "dashboard" => ['method' => 'get', 'protected' => true]
        ]);
    }

    
    protected function makePages(array $actions, string $namespace, string $class) {
        foreach ($actions as $action) {
            $this->makePage($action, $namespace, $class);
        }
    }

    protected function makePage(string $action, string $namespace, string $class, string $folder = 'auth') {
        $page = file_get_contents(__DIR__ . "/../../stubs/$folder/$action.stub");
        $page = str_replace("{{namespace}}", $namespace, $page);

        $page_name = "$namespace\\$class";

        file_put_contents($this->getPath($page_name . "$action"), $page);
    }

    protected function addRoutes(array $routes) {
        $file = file_get_contents(base_path('routes/drystack.php'));
        $file .= "\n";
        foreach ($routes as $route => $details) {
            $method = $details['method'];
            $protected = $details['protected'];
            $protected_middleware = $protected ? "->middleware(['auth'])" : "";
            if (str_contains($file, $route)) continue;
            $parts = explode(".", $route);
            $action = ucfirst(end($parts));
            $path = str_replace(".", "/", $route);
            $file .= "Route::$method('$path', {$action}Page::class){$protected_middleware}->name('$route');\n";
        }
        file_put_contents(base_path('routes/drystack.php'), $file);
    }
}
