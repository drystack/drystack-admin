<?php


namespace Drystack\Admin\Fields;


class FormWithSteps extends Form {

    /**
     * @var Step[] steps
     */
    protected array $steps = [];
    protected $number_of_steps = 1;

    public function addStep(Step $step) {
        $this->steps[$this->number_of_steps] = $step;
        $this->number_of_steps++;
        return $this;
    }

    public function build(): array {
        // TODO: Implement build() method.
    }

    public function hasFields(): bool {
        return count($this->steps) > 0;
    }

    public function prev() {

    }

    public function next() {

    }
}
