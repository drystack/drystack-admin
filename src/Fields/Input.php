<?php


namespace Drystack\Admin\Fields;


class Input extends Field {
    public static function make(string $model, ?string $label = null) {
        $self = new Input();
        $self->field_data['component'] = 'input';
        $self->field_data['model'] = $model;
        if (!empty($label)) $self->field_data['attributes']['label'] = $label;
        return $self;
    }

    public function withClass(string $classes) {
        $this->field_data['class'] = $classes;
        return $this;
    }

    public function withType(string $type) {
        $this->field_data['attributes']['type'] = $type;
        return $this;
    }

    public function withAttributes(array $attributes) {
        $this->field_data['attributes'] = array_merge($this->field_data['attributes'], $attributes);
        return $this;
    }

    public function withValidation(string $rules) {
        $this->field_data['rules'] .= '|' . $rules;
    }
}
