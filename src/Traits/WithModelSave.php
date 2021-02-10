<?php


namespace Drystack\Admin\Traits;


use Illuminate\Support\Facades\Route;

trait WithModelSave {

    public function submit() {
        $this->validate();
        $this->model->save();

        session()->flash('notification', __('drystack::drystack.notification.data.saved'));

        return redirect()->route(Route::current()->name . '.index');
    }
}
