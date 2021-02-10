<x-container>
    <x-column>
        <x-notification />
        <x-row class="justify-between mb-8 items-center max-w-screen-xl mx-auto">
            <x-title :breadcrumbs="[$title => [$resource, ['action' => 'index']]]"> {{ __('drystack::drystack.crud.read') }}</x-title>

            @if($this->isAllowedTo('update'))
            <x-button primary class="shadow">
                <a href="{{ route($resource, ['action' => 'update', 'model_id' => $model->id]) }}">{{ __('drystack::drystack.action.update') }} </a>
            </x-button>
            @endif
        </x-row>

        <x-card>

        </x-card>
    </x-column>
</x-container>
