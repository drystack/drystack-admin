<?php


namespace Drystack\Admin\Commands\Traits;

use Illuminate\Support\Str;

trait MakeFiles {
    protected function makeControllerAndViewFolders($namespace, $view_path) {
        $this->info($this->getPathNoExtension($namespace));
        $this->info($view_path);
        if (!file_exists($this->getPathNoExtension($namespace))) {
            mkdir($this->getPathNoExtension($namespace), 0777, true);
        }
        if (!file_exists($view_path)) {
            mkdir($view_path, 0777, true);
        }
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
}
