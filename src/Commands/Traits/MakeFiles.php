<?php


namespace Drystack\Admin\Commands\Traits;

use Illuminate\Support\Str;

trait MakeFiles {
    protected function makeControllerAndViewFolders($namespace, $view_path) {
        if (!is_dir($this->getPathNoExtension($namespace))) {
            mkdir($this->getPathNoExtension($namespace), 755, true);
        }
        if (!is_dir($view_path)) {
            mkdir($view_path, 755, true);
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