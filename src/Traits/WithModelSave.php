<?php


namespace Drystack\Admin\Traits;


use Illuminate\Support\Facades\Route;

trait WithModelSave {

    public function submit() {
        $this->validate();
        $this->model->save();
        $offset = strpos(Route::current()->name, ".") + 1;
        $length = strpos(Route::current()->name, "-") - $offset;
        $model_name = substr(Route::current()->name, $offset, $length);

        session()->flash('notification', __('notification.data-saved'));

        return redirect()->route($model_name . '.index');
    }
}
