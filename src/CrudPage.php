<?php

namespace Drystack\Admin;

use Drystack\Admin\Fields\Form;
use Drystack\Admin\Fields\FormInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

abstract class CrudPage extends Page {
    abstract public function form(): Form;
    protected $listeners = ['prev' => 'prev', 'next' => 'next'];

    public string $action;
    public ?string $resource;

    public $model_class;
    public $model;
    public $data = [];

    public string $title;

    public function mount(string $action, $model_id = null) {
        if (!$this->isAllowedTo($action)) {
            abort(401);
        }
        $this->action = $action;
        $this->resource = Route::current()->getName();
        if (!is_null($model_id)) {
            $this->model = $this->model_class::find($model_id);
            if (is_null($this->model)) {
                abort(404);
            }
        }
    }

    public function render() {
        $view_namespace = config('livewire.view_namespace');
        $view_prefix = str_replace("/", ".", substr($view_namespace, stripos('view/', $view_namespace) + 6));
        if (file_exists("$view_namespace/$this->resource/$this->action.blade.php")) {
            return view("$view_prefix.$this->resource.$this->action")->layout(config('drystack.layout'));
        }
        return match($this->action) {
            'index'     => view("dry-admin::index")->layout(config('drystack.layout')),
            'create'    => view('dry-admin::create')->layout(config('drystack.layout')),
            'read'      => view('dry-admin::read')->layout(config('drystack.layout')),
            'update'    => view('dry-admin::update')->layout(config('drystack.layout')),
            default     => abort(404)
        };
    }

//    protected function loadRelations() {
//        foreach($this->other_data as $other) {
//            $other::all()->each(fn($val) => $data[$other::class][$other->id] = $other);
//        }
//    }

    public function submit() {
        $this->isAllowedTo($this->action);
        $this->validate(
            $this->form()->getValidationRules()
        );
        $this->model->save();

        session()->flash('notification', __('drystack::drystack.notification.data.saved'));

        return redirect()->route(Route::current()->name . '.index');
    }

    public function isAllowedTo(string $action) {
        return Auth::user()->isAllowedTo($action, $this->model ?? $this->model_class);
    }

    public function prev() {
        if (isset($this->step)) $this->step--;
        $this->step;
    }

    public function next() {
        if (isset($this->step)) $this->step++;
        $this->step;
    }
}
