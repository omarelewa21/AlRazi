<div class="grid grid-cols-10 font-serif h-full">
    @include('livewire.pages.image-diagnose.patient-info-menu')

    <!-- Image Display -->
    <div class="col-span-5 overflow-hidden">
        <!-- Toolbar -->
        <div class="flex justify-center bg-gray-200 p-2">
            <x-image-toolbar.visibility-toggle :$renderImages/>
            <x-image-toolbar.length />
        </div>

        <div class="flex justify-center mt-5">
            @foreach ($renderImages as $image)
                @if ($image['visibility'])
                    <img src="{{ $image['url'] }}" alt="X-Ray Image" class="absolute">
                @endif
            @endforeach
        </div>
    </div>

    @include('livewire.pages.image-diagnose.observations-menu')
</div>
