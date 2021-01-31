<?php


namespace Drystack\Admin\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class DrystackAdminInit extends Command{
    protected $signature = 'drystack:admin:init';

    protected $description = 'Init Drystack admin';

    public function handle() {
        $this->warn("Drystack depends on Livewire. Please run 'livewire:publish --config' before continuing.");
        $continue = $this->confirm("Proceed?", true);
        if (!$continue) return 0;
        $this->info('Drystack will generate files in livewire\'s view and namespace folders');
        $this->info("To initialize Drystack admin the following steps are required:");
        $this->info("1- run migrations with php artisan migrate");
        $this->info("2- add the WithPermissions trait to your User model");
        $this->info("3- update Livewire's namespace and view_path configurations if you don't with to use the default values");
        $this->warn("Please confirm to proceed if you completed the steps.");
        $continue = $this->confirm("Proceed?", true);
        if (!$continue) return 0;

        sleep(1);

        $this->callSilent('vendor:publish', ['--tag' => 'drystack-compiled-assets', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-menu', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-lang', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'drystack-config', '--force' => true]);

        $file = file_get_contents(base_path('routes/web.php'));
        if (!str_contains($file, "require __DIR__.'/drystack.php';")) {
            $file .= "\n";
            $file .= "require __DIR__ . '/drystack.php';";
            file_put_contents(base_path('routes/web.php'), $file);
        }
        if (!file_exists(base_path('routes/drystack.php'))) {
            copy(__DIR__ . '/../../routes/drystack.php', base_path('routes/drystack.php'));
        }

        if (!Schema::hasTable('prm_abilities')) {
            $this->error("Init failes. Permissions tables not found. Please run 'php artisan migrate'");
            return -1;
        }

        $this->call('drystack:admin:auth');
        $this->call('drystack:admin:crud', ['name' => 'User']);

        copy(__DIR__ . '/../../stubs/menu.stub', resource_path('views/vendor/drystack/menu.blade.php'));
    }
}
