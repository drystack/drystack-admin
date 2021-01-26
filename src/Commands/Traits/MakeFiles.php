<?php


namespace Drystack\Admin\Commands\Traits;


trait MakeFiles {
    protected function makeControllerAndViewFolders($namespace, $view_path) {
        if (!is_dir($this->getPathNoExtension($namespace))) {
            mkdir($this->getPathNoExtension($namespace), 655);
        }
        if (!is_dir($view_path)) {
            mkdir($view_path, 655);
        }
    }
}