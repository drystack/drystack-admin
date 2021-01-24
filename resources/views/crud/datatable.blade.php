<div>
    @if($beforeTableSlot)
        <div class="mt-8">
            @include($beforeTableSlot)
        </div>
    @endif
    <div class="relative acsjlmk">
        <div class="flex justify-between items-center mb-1">
            <div class="flex-grow h-10 flex items-center">
                @if($this->searchableColumns()->count())
                    <div class="w-96 flex rounded">
                        <div class="relative flex-grow focus-within:z-10">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-neutral-400" viewBox="0 0 20 20" stroke="currentColor" fill="none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <x-dry-input-base wire:model.debounce.500ms="search"  placeholder="Cerca in {{ $this->searchableColumns()->map->label->join(', ') }}" />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button wire:click="$set('search', null)" class="text-neutral-300 hover:text-red-600 focus:outline-none">
                                    <x-icons.x-circle class="h-5 w-5 stroke-current" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center space-x-1">
                <x-icons.cog wire:loading class="h-9 w-9 animate-spin text-neutral-400" />

                @if($exportable)
                    <div x-data="{ init() {
                    window.livewire.on('startDownload', link => window.open(link,'_blank'))
                } }" x-init="init">
                        <x-dry-button success outline wire:click="export">
                            <span class="text-sm flex flex-row items-center">EXPORT <x-icons.excel class="ml-2" /></span>
                        </x-dry-button>
                    </div>
                @endif

                @if($hideable === 'select')
                    @include('datatables::hide-column-multiselect')
                @endif
            </div>
        </div>

        @if($hideable === 'buttons')
            <div class="p-2 grid grid-cols-8 gap-2">
                @foreach($this->columns as $index => $column)
                    <button wire:click.prefetch="toggle('{{ $index }}')" class="px-3 py-2 rounded text-white text-xs focus:outline-none
            {{ $column['hidden'] ? 'bg-primary-100 hover:bg-primary-300 text-primary-600' : 'bg-primary-500 hover:bg-primary-800' }}">
                        {{ $column['label'] }}
                    </button>
                @endforeach
            </div>
        @endif

        <div class="rounded bg-white max-w-screen overflow-x-scroll">
            <div class="rounded @unless($this->hidePagination) rounded-b-none @endif">
                <div class="table align-middle min-w-full">
                    @unless($this->hideHeader)
                        <div class="table-row divide-x divide-neutral-200">
                            @foreach($this->columns as $index => $column)
                                @if($hideable === 'inline')
                                    @include('drystack::crud.header-inline-hide', ['column' => $column, 'sort' => $sort])
                                @elseif($column['type'] === 'checkbox')
                                    <div class="relative table-cell h-12 w-48 overflow-hidden align-top px-6 py-3 border-b border-neutral-200 bg-neutral-50 text-left text-xs leading-4 font-medium text-neutral-500 uppercase tracking-wider flex items-center focus:outline-none">
                                        <div class="px-3 py-1 rounded @if(count($selected)) bg-secondary-400 @else bg-neutral-200 @endif text-white text-center">
                                            {{ count($selected) }}
                                        </div>
                                    </div>
                                @else
                                    @include('drystack::crud.header-no-hide', ['column' => $column, 'sort' => $sort])
                                @endif
                            @endforeach
                        </div>

                        <div class="table-row divide-x divide-primary-200 bg-primary-50">
                            @foreach($this->columns as $index => $column)
                                @if($column['hidden'])
                                    @if($hideable === 'inline')
                                        <div class="table-cell w-5 overflow-hidden align-top bg-primary-100"></div>
                                    @endif
                                @elseif($column['type'] === 'checkbox')
                                    <div class="w-32 overflow-hidden align-top bg-primary-100 px-6 py-5 border-b border-neutral-200 bg-neutral-50 text-left text-xs leading-4 font-medium text-neutral-500 uppercase tracking-wider flex h-full flex-col items-center space-y-2 focus:outline-none">
                                        <div>SELECT ALL</div>
                                        <div>
                                            <input type="checkbox" wire:click="toggleSelectAll" @if(count($selected) === $this->results->total()) checked @endif class="form-checkbox mt-1 h-4 w-4 text-primary-600 transition duration-150 ease-in-out" />
                                        </div>
                                    </div>
                                @else
                                    <div class="table-cell overflow-hidden align-top">
                                        @isset($column['filterable'])
                                            @if( is_iterable($column['filterable']) )
                                                <div wire:key="{{ $index }}">
                                                    @include('datatables::filters.select', ['index' => $index, 'name' => $column['label'], 'options' => $column['filterable']])
                                                </div>
                                            @else
                                                <div wire:key="{{ $index }}">
                                                    @include('datatables::filters.' . ($column['filterView'] ?? $column['type']), ['index' => $index, 'name' => $column['label']])
                                                </div>
                                            @endif
                                        @endisset
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @forelse($this->results as $result)
                        <div class="table-row p-1 divide-x divide-neutral-100 {{ isset($result->checkbox_attribute) && in_array($result->checkbox_attribute, $selected) ? 'bg-secondary-100' : ($loop->even ? 'bg-white' : 'bg-neutral-50') }} hover:bg-neutral-100">
                            @foreach($this->columns as $column)
                                @if($column['hidden'])
                                    @if($hideable === 'inline')
                                        <div class="table-cell w-5 overflow-hidden align-top"></div>
                                    @endif
                                @elseif($column['type'] === 'checkbox')
                                    @include('datatables::checkbox', ['value' => $result->checkbox_attribute])
                                @else
                                    <div class="px-6 py-2 whitespace-no-wrap text-sm leading-5 text-neutral-900 table-cell @if($column['align'] === 'right') text-right @elseif($column['align'] === 'center') text-center @else text-left @endif">
                                        {!! $result->{$column['name']} !!}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @empty
                        <p class="p-3 text-neutral-600">
                            Nessun risultato
                        </p>
                    @endforelse
                </div>
            </div>
            @unless($this->hidePagination)
                <div class="rounded rounded-t-none max-w-screen rounded bg-white">
                    <div class="p-2 sm:flex items-center justify-between">
                        {{-- check if there is any data --}}
                        @if($this->results[1])
                            <div class="my-2 sm:my-0 flex items-center">
                                <select name="perPage" class="mt-1 form-select block w-full pl-3 pr-10 py-2 text-base leading-6 border-neutral-300 focus:outline-none focus:shadow-outline-blue focus:border-primary-300 sm:text-sm sm:leading-5" wire:model="perPage">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="99999999">All</option>
                                </select>
                            </div>

                            <div class="my-4 sm:my-0">
                                <div class="lg:hidden">
                                    <span class="space-x-2">{{ $this->results->links('datatables::tailwind-simple-pagination') }}</span>
                                </div>

                                <div class="hidden lg:flex justify-center">
                                    <span>{{ $this->results->links('datatables::tailwind-pagination') }}</span>
                                </div>
                            </div>

                            <div class="flex justify-end text-neutral-600">
                                Results {{ $this->results->firstItem() }} - {{ $this->results->lastItem() }} of
                                {{ $this->results->total() }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
    @if($afterTableSlot)
        <div class="mt-8">
            @include($afterTableSlot)
        </div>
    @endif
</div>
