<?php


namespace Drystack\Admin\Commands;


class DrystackAdminInit {
    protected $signature = 'drystack:crud:init';

    protected $description = 'Init Drystack admin';

    public function handle() {
        $this->callSilent('vendor:publish', ['--provider' => 'Laravel\Fortify\FortifyServiceProvider', '--force' => true]);
    }
}
