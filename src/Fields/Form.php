<?php


namespace Drystack\Admin\Fields;


class Form {
    /**
     * @var Field[] fields
     */
    protected $fields = [];

    /**
     * @var Step[] steps
     */
    protected $steps = [];

    protected $current_step = 1;

    private function __construct() {}

    /**
     * @param Step[] $steps
     * @return Form
     */
    public static function withSteps($steps) {
        $form = new Form();
        $i = 1;
        foreach ($steps as $step) {
            $form->steps[$i] = $step;
            $i++;
        }
        return $form;
    }

    /**
     * @param Field[] $fields
     * @return Form
     */
    public static function withFields($fields) {
        $form = new Form();
        $form->fields = $fields;
        return $form;
    }


    public function build(): array {
        $fields = [];
        if (!$this->hasSteps()) {
            foreach ($this->fields as $field) {
                $fields[] = $field->build();
            }
        } else {
//            foreach ($this->steps as $step) {
//                $step->
//            }
        }
    }

    public function hasFields(): bool {
        return count($this->fields) > 0;
    }

    public function hasSteps(): bool {
        return count($this->steps) > 0;
    }

    public function prevStep() {
        if ($this->current_step <= 1) return;
        $this->current_step--;
    }

    public function nextStep() {
        if ($this->current_step >= count($this->steps)) return;
        $this->current_step++;
    }

    public function getCurrentStep(): int {
        return $this->current_step;
    }

    public function getSteps() {
        return $this->steps;
    }

    public function getValidationRules(): array {

    }
}
