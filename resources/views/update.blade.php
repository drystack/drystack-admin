<x-container>
    <x-column>
        <x-notification />
        <x-row class="justify-between mb-8 items-center max-w-screen-xl  mx-auto">
            <x-title :breadcrumbs="[$title => [$resource, ['action' => 'index']]]"> {{ __('drystack::drystack.crud.update') }}</x-title>
        </x-row>

        <x-card>
            <x-form wire:submit.prevent="submit">

            </x-form>
        </x-card>
    </x-column>
</x-container>
