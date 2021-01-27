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
    }
}
