<?php


namespace Drystack\Admin\Commands;

use Database\Factories\UserFactory;
use Drystack\Admin\Commands\Traits\HasLivewire;
use Drystack\Admin\Commands\Traits\MakeFiles;
use Drystack\Admin\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MakeAuth extends Command {
    use HasLivewire, MakeFiles;

    protected $signature = 'drystack:admin:auth';

    protected $description = 'Setup Drystack admin scaffolding';

    public function handle() {
        if ($this->checkLivewireConfigured() == -1) return -1;
        $dash_namespace = $this->livewire_namespace . "\\Dashboard";
        $dash_view_path = $this->livewire_view_path . "/dashboard";
        $profile_namespace = $this->livewire_namespace . "\\Profile";
        $profile_view_path = $this->livewire_view_path . "/profile";
        $this->livewire_view_path = $this->livewire_view_path . "/auth";
        $this->livewire_namespace = $this->livewire_namespace . "\\Auth";
        $this->makeControllerAndViewFolders($this->livewire_namespace, $this->livewire_view_path);
        $this->makeControllerAndViewFolders($dash_namespace, $dash_view_path);
        $this->makeControllerAndViewFolders($profile_namespace, $profile_view_path);

        copy(__DIR__ . '/../../stubs/auth/login.stub', $this->livewire_view_path . "/login.blade.php");
        copy(__DIR__ . '/../../stubs/auth/forgot-password.stub', $this->livewire_view_path . "/forgot-password.blade.php");
        copy(__DIR__ . '/../../stubs/auth/reset-password.stub', $this->livewire_view_path . "/reset-password.blade.php");
        copy(__DIR__ . '/../../stubs/dashboard/dashboard.stub', $dash_view_path . "/dashboard.blade.php");
        copy(__DIR__ . '/../../stubs/profile/profile.stub', $profile_view_path . "/profile.blade.php");

        $this->makePages(["LoginPage", "ForgotPasswordPage", "ResetPasswordPage", "LogoutPage"], $this->livewire_namespace, "Auth");
        $this->makePage("DashboardPage", $dash_namespace, "Dashboard", 'dashboard');
        $this->makePage("ProfilePage", $profile_namespace, "Profile", 'profile');
        $this->addRoutes([
            "login" => ['method' => 'get', 'protected' => false, 'action' => $this->livewire_namespace . '\\LoginPage'],
            "password.forgot" => ['method' => 'get', 'protected' => false, 'action' => $this->livewire_namespace . '\\ForgotPasswordPage', 'path' => 'forgot-password'],
            "password.reset" => ['method' => 'get', 'protected' => false, 'action' => $this->livewire_namespace . '\\ResetPasswordPage', 'path' => 'reset-password/{token}'],
            "logout" => ['method' => 'get', 'protected' => true, 'action' => $this->livewire_namespace . '\\LogoutPage'],
            "dashboard" => ['method' => 'get', 'protected' => true, 'action' => $dash_namespace . '\\DashboardPage'],
            "profile" => ['method' => 'get', 'protected' => true, 'action' => $profile_namespace . '\\ProfilePage']
        ]);

        $role = Role::find(1);
        if ($role == null) {
            $role = new Role();
            $role->name = "Administrator";
            $role->save();
        }

        $create = true;
        if (\App\Models\User::count() > 1) {
            $create = $this->confirm("Do you want to create a new admin user?", false);

        }
        if ($create) {
            $this->info("Create admin user");
            $name = $this->ask("Insert admin name:", "Administrator");
            $email = $this->ask("Insert email:", null);
            $password = $this->ask("Insert password:", null);

            $user = new \App\Models\User();
            $user->name = $name;
            $user->email = $email;
            $user->email_verified_at = now();
            $user->password = Hash::make($password);
            $user->remember_token = Str::random(10);
            $user->save();

            $user->addRole($role->name);
        }
    }


    protected function makePages(array $actions, string $namespace, string $class) {
        foreach ($actions as $action) {
            $this->makePage($action, $namespace, $class);
        }
    }

    protected function makePage(string $action, string $namespace, string $class, string $folder = 'auth') {
        $page = file_get_contents(__DIR__ . "/../../stubs/$folder/$action.stub");
        $page = str_replace("{{namespace}}", $namespace, $page);
        $page = str_replace("{{prefix}}", $this->view_prefix, $page);

        $page_name = "$namespace\\$action";

        file_put_contents($this->getPath($page_name), $page);
    }

    protected function addRoutes(array $routes) {
        $file = file_get_contents(base_path('routes/drystack.php'));
        $file .= "\n";
        foreach ($routes as $route => $details) {
            $method = $details['method'];
            $protected = $details['protected'];
            $protected_middleware = $protected ? "->middleware(['auth'])" : "->middleware(['guest'])";
            if (str_contains($file, $route)) continue;
            $action = $details['action'];
            $path = $details['path'] ?? str_replace(".", "/", $route);
            $file .= "Route::$method('$path', {$action}::class){$protected_middleware}->name('$route');\n";
        }
        file_put_contents(base_path('routes/drystack.php'), $file);
    }
}
