<?php


namespace Drystack\Admin\Fields;


abstract class Field {
    protected $field_data = [
        'component' => '', //component name
        'only' => [], //list of actions for which the component is allowed
        'model' => '', //livewire model
        'class' => '', //css classes to add to component
        'attributes' => [], //array of attributes, key is attr.name, value is the value
        'rules' => ''
    ];

    abstract public static function make(string $model, ?string $label = null);
    abstract public function withClass(string $classes);
    abstract public function withAttributes(array $attributes);
    abstract public function withValidation(string $rules);

    public function createOnly(bool $create_only) {
        if ($create_only)
            $this->field_data['only'] = array_merge($this->field_data['only'], ['create']);
        elseif ($i = array_search('create', $this->field_data['only'])) {
            unset($this->field_data['only'][$i]);
        }
        return $this;
    }
    public function updateOnly(bool $update_only) {
        if ($update_only)
            $this->field_data['only'] = array_merge($this->field_data['only'], ['update']);
        elseif ($i = array_search('update', $this->field_data['only'])) {
            unset($this->field_data['only'][$i]);
        }
        return $this;
    }

    public function build() {
        return $this->field_data;
    }
}
