<?php


namespace Drystack\Admin\Fields;


interface FormInterface {
    public function build(): array;
    public function hasFields(): bool;
}
