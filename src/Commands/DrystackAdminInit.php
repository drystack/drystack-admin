<?php


namespace Drystack\Admin\Commands;

use Illuminate\Console\Command;

class DrystackAdminInit extends Command{
    protected $signature = 'drystack:admin:init';

    protected $description = 'Init Drystack admin';

    public function handle() {
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-compiled-assets', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-menu', '--force' => true]);
        $this->callSilent('vendor:publish', ['--provider' => 'Laravel\Fortify\FortifyServiceProvider', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-lang', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-config', '--force' => true]);
        $this->callSilent('livewire:publish', ['--config' => '']);

        $file = file_get_contents(base_path('routes/web.php'));
        if (!str_contains($file, "require __DIR__.'/drystack.php';")) {
            $file .= "\n";
            $file .= "require __DIR__ . '/drystack.php';";
            file_put_contents(base_path('routes/web.php'), $file);
        }
        if (!file_exists(base_path('routes/drystack.php'))) {
            copy(__DIR__ . '/../../routes/drystack.php', base_path('routes/drystack.php'));
        }
    }
}
