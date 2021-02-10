<x-container>
    <x-column>
        <x-notification />
        <x-row class="justify-between mb-8 items-center max-w-screen-xl mx-auto">
            <x-title>{{__($title)}} </x-title>
            <x-button primary class="shadow">
                <a href="{{ route($resource, ['action' => 'create']) }}">{{ __('drystack::drystack.action.create') }} </a>
            </x-button>
        </x-row>

        <x-card>
            @livewire("{$resource}.{$resource}-datatable")
        </x-card>
    </x-column>
</x-container>
