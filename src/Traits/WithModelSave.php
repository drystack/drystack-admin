<?php


namespace Drystack\Crud\Traits;


use Illuminate\Support\Facades\Route;

trait WithModelSave {

    public function submit() {
        $this->validate();
        $this->model->save();

        $model_name = substr(Route::current()->name, 0, strpos(Route::current()->name, "-"));

        session()->flash('notification', ucfirst($model_name) . ' saved successfully');

        return redirect()->route($model_name . '.index');
    }
}
