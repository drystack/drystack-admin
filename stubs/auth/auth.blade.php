<x-center>
    <div class="max-w-sm flex flex-1">
        <x-card color="primary">
            @if(config('drystack.logo') != null)
                <img class="h-12 mx-auto" src="{{config('drystack.logo')}}">
            @endif

            <x-form wire:submit.prevent="submit">
                <x-input label="{{__('email')}}" field="email" type="email"/>
                <x-input label="{{__('password')}}" field="password" type="password"/>

                <x-slot name="actions">
                    <x-row class="justify-end mt-6 items-center gap-6">
                        <x-link class="text-neutral-600">{{ __('forgot-password') }}</x-link>
                        <x-button type="submit">{{ __('send') }}</x-button>
                    </x-row>
                </x-slot>
            </x-form>

        </x-card>
    </div>
</x-center>
