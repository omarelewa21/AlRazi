<div class="grid grid-cols-12 font-serif h-full min-h-screen" x-data="diagnoseImage()">
    @include('livewire.pages.image-diagnose.patient-info-menu')
    <!-- Image Display -->
    <div class="col-span-7 overflow-hidden">
        <!-- Toolbar -->
        <div class="flex justify-center bg-gray-200 p-2 min-h-12">
            @if(!empty($diagnoseImages))
                @include('components.image-toolbar.visibility-toggle')
            @endif
            @if (!empty($sourceImgs))
                @include('components.image-toolbar.zoom')
            @endif
            @if(!empty($sourceImgs))
                <span class="outline outline-2 outline-blue-500 hidden"></span>
                <div class="cursor-pointer flex items-center rounded-md shadow px-5 py-2 ml-4"
                    @click="toggleLengthTool($el)"
                    title="Length Tool"
                >
                    <x-icons.ruler />
                </div>
            @endif
        </div>

        <div class="flex flex-row flex-wrap justify-center mt-5" x-ref="imagesContainer">
            @foreach ($sourceImgs as $key => $sourceImg)
                <div class="flex justify-center image-container" id="image-container" x-data="{imgWidth: 0, imgHeight: 0}"
                    @wheel.throttle.prevent="zoom"
                >
                    <img src="{{ $sourceImg['url'] }}" alt="X-Ray Image" class="relative box-1-image" id="source-image" x-ref="source-image"
                        x-init="imgWidth = $el.width; imgHeight = $el.height;"
                    >
                    @if(array_key_exists($key, $diagnoseImages))
                        @foreach ($diagnoseImages[$key] as $k => $image)
                            @if ($image['visibility'])
                                <img src="{{ $image['url'] }}" alt="X-Ray Image" class="absolute box-1-image"
                                    x-init="images['{{ "$key.$k" }}'] = true"
                                    x-show="images['{{ "$key.$k" }}']"
                                >
                            @endif
                        @endforeach
                    @endif
                    <svg class="absolute" :height="imgHeight" :width="imgWidth"
                        x-init="addSvgToTools({{$loop->index}}, {{0.5}})"
                        @click="startLineDraw(event, {{$loop->index}})"
                        @mousemove="continueLineDraw(event, {{$loop->index}})"
                    >
                        <text x="10" y="20" fill="#ffffff" font-size="20px">
                            {{ $loop->index + 1 }}
                        </text>
                    </svg>
                </div>
            @endforeach
        </div>

    </div>

    @include('livewire.pages.image-diagnose.observations-menu')

    <div wire:loading wire:target="processDiagnosis, files, showReport">
        <x-loading />
    </div>
</div>
