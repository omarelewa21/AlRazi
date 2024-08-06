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
        <div class="ml-4 flex flex-col">
            @foreach ($this->singleCases() as $view => $cases)
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 leading-tight py-4 px-2">
                    {{ $view }}
                </h1>

                <div class="grid grid-cols-3 gap-2">
                    @foreach ($cases as $case)
                        <div class="cursor-pointer p-2" @click="toggleAdd('{{ $case['id'] }}')"
                            :class="diagnosesToAdd.includes('{{ $case['id'] }}') ? 'outline outline-2 outline-blue-500 rounded-md' : ''"
                        >
                            <img src="{{ $case['url'] }}" alt="{{ $case['view'] }}" class="w-52 h-52 object-cover">
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 leading-tight py-4 px-2">
            Compound Cases
       </h1>
        <div class="grid grid-cols-2 gap-4 mx-2">
            @foreach ($this->compoundCases() as $cases)
                @php $id = reset($cases)['id'] @endphp
                <div class="flex flex-row justify-between cursor-pointer p-2" @click="toggleAdd('{{ $id }}')"
                    :class="diagnosesToAdd.includes('{{ $id }}') ? 'outline outline-2 outline-blue-500 rounded-md' : ''"
                >
                    @foreach ($cases as $case)
                        <div class="flex flex-col">
                            <img src="{{ $case['url'] }}" alt="{{ $case['view'] }}" class="w-48 h-52 object-cover">
                            <p> {{ $case['view'] }} </p>
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
