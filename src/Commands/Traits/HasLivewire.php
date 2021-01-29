<?php


namespace Drystack\Admin\Commands\Traits;


trait HasLivewire {

    public string $livewire_namespace = '';
    public string $livewire_view_path = '';
    public string $view_prefix = '';

    public function checkLivewireConfigured() {
        if (empty(config('livewire.class_namespace'))) {
            $this->error("Livewire's class_namespace is not configured for your project. Please configure Livewire");
            return -1;
        } else {
            $this->livewire_namespace = config('livewire.class_namespace');
        }

        if (empty(config('livewire.view_path'))) {
            $this->error("Livewire's view_path is not configured for your project. Please configure Livewire");
            return -1;
        } else {
            $this->livewire_view_path = config('livewire.view_path');

            $view_path_laravel = substr($this->livewire_view_path, stripos($this->livewire_view_path, "views/") + 6);
            $this->view_prefix = str_replace('/', '.', "$view_path_laravel");
        }
    }
}
