<?php


namespace Drystack\Admin\Fields;


class Step {
    protected $step_data = [
        'component' => 'step',
        'visible' => false,
        'first' => false,
        'last' => false,
        'title' => '',
        'subtitle' => '',
        'action' => null,
        'children' => []
    ];

    /**
     * @param Field[]  $children
     * @return mixed
     */
     public static function make(array $children) {
         $self = new Step();
//         $self->step_data['visible'] = $visible;
//         $self->step_data['first'] = $first;
//         $self->step_data['last'] = $last;
         $self->step_data['children'] = $children;
         return $self;
     }

     public function withTitle(string $title) {
         $this->step_data['title'] = $title;
         return $this;
     }

    public function withSubitle(string $subtitle) {
        $this->step_data['subtitle'] = $subtitle;
        return $this;
    }

    public function withAction(string $action) {
        $this->step_data['action'] = $action;
        return $this;
    }

     public function build() {
         return $this->step_data;
     }

     public function getValidationRule() {

     }
}
