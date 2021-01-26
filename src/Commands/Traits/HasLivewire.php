<?php


namespace Drystack\Admin\Commands\Traits;


trait HasLivewire {

    public string $namespace;
    public string $view_path;

    public function checkLivewireConfigured() {
        if (empty(config('livewire.class_namespace'))) {
            $this->error("Livewire's class_namespace is not configured for your project. Please configure Livewire");
            return -1;
        } else {
            $this->namespace = config('livewire.class_namespace');
        }

        if (empty(config('livewire.view_path'))) {
            $this->error("Livewire's view_path is not configured for your project. Please configure Livewire");
            return -1;
        } else {
            $this->view_path = config('livewire.view_path');
        }
    }
}