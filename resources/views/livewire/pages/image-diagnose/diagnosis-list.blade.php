<div>
    <div class="flex flex-row justify-between mb-4">
        <h2 class="text-2xl font-medium">@lang('Diagnose Worklist')</h2>
        <div>
            <button @click="window.open('{{ route('diagnose.create') }}', '_blank')" class="text-base bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                @lang('New Case')
            </button>
            <button class="text-base bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" wire:click="$dispatch('openModal', {component: 'pages.image-diagnose.quick-upload'})">
                @lang("Quick Upload")
            </button>
        </div>

    </div>


    {{ $this->table }}
</div>
