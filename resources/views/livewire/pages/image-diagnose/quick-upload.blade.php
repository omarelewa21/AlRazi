<div x-init="document.querySelector('#modal-container').style.maxWidth = '45rem'" class="p-2">
    <h1 class="text-2xl px-2 pt-2 dark:text-gray-200">
        @lang('Quick Upload')
    </h1>
    <h2 class="text-sm mb-1 p-2 pl-2 dark:text-gray-200 text-red-500">
        @lang('Upload DCM Files - Max: 10 Cases')
    </h2>
    <form wire:submit.prevent="process" class="flex flex-row justify-between items-end p-2 mb-2">
        <div class="flex flex-col">
            <input type="file" wire:model="files" required multiple tabindex="-1">
            @error('files') <span class="text-red-500">{{ $message }}</span> @enderror
            @error('files.*') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            @lang('Upload')
        </button>
    </form>

    <div wire:loading wire:target="files, process">
        <x-loading />
    </div>
</div>


