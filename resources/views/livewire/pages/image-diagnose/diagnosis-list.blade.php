<div>
    <div class="flex flex-row justify-between mb-4">
        <h2 class="text-2xl font-medium">Worklist</h2>
        <a href="{{ route('diagnose.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            {{ __("Create New Diagnose") }}
        </a>
    </div>


    {{ $this->table }}
</div>
