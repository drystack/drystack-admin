<x-container>
    <x-column>
        <x-notification />
        <x-row class="justify-between mb-8 items-center max-w-screen-xl mx-auto">
            <x-title :breadcrumbs="[$title => [$resource, ['action' => 'index']]]"> {{ __('drystack::drystack.crud.create') }}</x-title>
        </x-row>

        <x-card>
            <a href="#" wire:click="setShow()">Show</a>
            <x-form wire:submit.prevent="submit" :fields="$this->form()" :pageaction="$action" :steps="$this->form()->hasSteps()"/>
        </x-card>
    </x-column>
</x-container>
