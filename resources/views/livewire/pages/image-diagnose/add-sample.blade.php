<div class="flex flex-col p-4"
    x-data="{
        diagnosesToAdd: @entangle('diagnosesToAdd'),
        toggleAdd(id) {
            if (this.diagnosesToAdd.includes(id)) {
                this.diagnosesToAdd = this.diagnosesToAdd.filter(item => item !== id);
            } else {
                this.diagnosesToAdd.push(id);
            }
        }
    }"
    x-init="document.querySelector('#modal-container').style.maxWidth = '45rem'"
>
    <div class="flex flex-col">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 leading-tight p-4">
             Single Cases
        </h1>
        <div class="grid grid-cols-3 gap-2 ml-4">
            @foreach ($this->singleCases() as $case)
                @foreach ($case as $data)
                    <div class="flex flex-col cursor-pointer p-2" @click="toggleAdd('{{ $data['id'] }}')"
                        :class="diagnosesToAdd.includes('{{ $data['id'] }}') ? 'outline outline-2 outline-blue-500 rounded-md' : ''"
                    >
                        <img src="{{ $data['url'] }}" alt="{{ $data['view'] }}" class="w-52 h-52 object-cover">
                        <p> {{ $data['view'] }} </p>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <div class="mt-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 leading-tight py-4 px-2">
            Compound Cases
       </h1>
        <div class="grid grid-cols-2 gap-4 mx-2">
            @foreach ($this->compoundCases() as $case)
                @php $id = reset($case)['id'] @endphp
                <div class="flex flex-row justify-between cursor-pointer p-2" @click="toggleAdd('{{ $id }}')"
                    :class="diagnosesToAdd.includes('{{ $id }}') ? 'outline outline-2 outline-blue-500 rounded-md' : ''"
                >
                    @foreach ($case as $data)
                        <div class="flex flex-col">
                            <img src="{{ $data['url'] }}" alt="{{ $data['view'] }}" class="w-48 h-52 object-cover">
                            <p> {{ $data['view'] }} </p>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-4 flex justify-end" x-show="diagnosesToAdd.length > 0">
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" wire:click="addSamplesToWorkList">
            @lang('Add Demo Cases')
        </button>
    </div>

    <div wire:loading wire:target="addSamplesToWorkList">
        <x-loading />
    </div>
</div>
