<?php


namespace Drystack\Admin\Commands;

use Drystack\Admin\Commands\Traits\HasLivewire;
use Drystack\Admin\Commands\Traits\MakeFiles;
use Illuminate\Console\Command;

class DrystackAdminInit extends Command{

    use HasLivewire, MakeFiles;

    protected $signature = 'drystack:admin:init';

    protected $description = 'Init Drystack admin';

    public function handle() {
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-compiled-assets', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-menu', '--force' => true]);
        $this->callSilent('vendor:publish', ['--provider' => 'Laravel\Fortify\FortifyServiceProvider', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-lang', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-config', '--force' => true]);
        $this->callSilent('livewire:publish', ['--config' => '']);

        if ($this->checkLivewireConfigured() == -1) return -1;
        
        $this->view_path = $this->view_path . "/auth";
        $this->namespace = $this->namespace . "\\Auth";
        $this->makeControllerAndViewFolders($this->namespace, $this->view_path);
        
        copy(__DIR__ . '/../../stubs/auth/auth.stub', $this->view_path . "/auth.blade.php");
        copy(__DIR__ . '/../../stubs/auth/forgot-password.stub', $this->view_path . "/forgot-password.blade.php");
        copy(__DIR__ . '/../../stubs/auth/reset-password.stub', $this->view_path . "/reset-password.blade.php");

        $this->makePages(["AuthPage", "ForgotPasswordPage", "ResetPasswordPage"], $this->namespace, "Auth");
    }

    protected function makePages(array $actions, string $namespace, string $class) {
        foreach ($actions as $action) {
            $this->makePage($action, $namespace, $class);
        }
    }

    protected function makePage(string $action, string $namespace, string $class) {
        $page = file_get_contents(__DIR__ . "/../../stubs/auth/$action.stub");
        $page = str_replace("{{namespace}}", $namespace, $page);

        $page_name = "$namespace\\$class";

        file_put_contents($this->getPath($page_name . "$action"), $page);
    }
}
