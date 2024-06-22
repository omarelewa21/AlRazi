<div class="grid grid-cols-10 gap-4 mt-5 font-serif">
    <!-- User Left Sidebar Input Form -->
    <div class="col-span-2 ml-2 bg-gray-200">
        <h3 class="text-lg bg-blue-500 mb-3 p-1 pl-2 font-semibold text-white dark:text-gray-200 leading-tight">
            @lang('Patient Information')
        </h3>
        <form wire:submit.prevent="processDiagnosis" class="flex flex-col p-2">
            <div>
                <label for="name" class="font-semibold block required">@lang('Name')</label>
                <input type="text" wire:model="name" required class="rounded w-full">
                @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mt-3">
                <label for="gender" class="font-semibold mt-1 block required">@lang('Gender')</label>
                <div class="flex flex-row justify-around">
                    <div>
                        <input type="radio" name="gender" value="male" wire:model="gender"> @lang('Male')
                    </div>
                    <div>
                        <input type="radio" name="gender" value="female" wire:model="gender"> @lang('Female')
                    </div>
                    <div>
                        <input type="radio" name="gender" value="other" wire:model="gender"> @lang('Other')
                    </div>
                </div>
                @error('gender') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mt-3">
                <label for="date_of_birth" class="font-semibold block required">@lang('Date of Birth')</label>
                <input wire:model="date_of_birth" type="date" class="rounded w-full">
                @error('date_of_birth') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mt-3">
                <label for="email" class="font-semibold block required">@lang('Email')</label>
                <input type="email" wire:model="email" required class="rounded w-full">
                @error('email') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mt-3">
                <label for="phone" class="font-semibold block required">@lang('Phone')</label>
                <input type="tel" wire:model="phone" required class="rounded w-full">
                @error('phone') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mt-3">
                <label for="referral" class="font-semibold block">@lang('Referral')</label>
                <input type="text" wire:model="referral" class="rounded w-full">
                @error('referral') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mt-10">
                <label for="file" class="font-semibold block required">@lang('Upload X-Ray Image')</label>
                <input type="file" wire:model="file" required class="rounded w-full">
                @error('file') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mt-3">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    @lang('Process Diagnosis')
                </button>
            </div>
        </form>
    </div>

    <!-- Image Display -->
    <div class="col-span-6 cornerstone-element-wrapper flex justify-center images-content">
        <div class="cornerstone-element" data-index="0" oncontextmenu="return false"></div>
    </div>

    <!-- Right Sidebar Table Information  -->
    <div class="col-span-2 mr-2 bg-gray-200 flex flex-col">
        <h3 class="text-lg bg-blue-500 mb-3 p-1 pl-2 font-semibold text-white dark:text-gray-200 leading-tight">
            @lang('Observations')
        </h3>
        @if (!empty($observations) && false)
            <div class="overflow-auto mb-10 m-2">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="border border-gray-400 px-4 py-2 text-left">@lang('Variable')</th>
                            <th class="border border-gray-400 px-4 py-2 text-left">@lang('Observation')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($observations as $observation)
                            <tr>
                                <td class="border border-gray-400 px-4 py-2 text-left">{{ $observation['variable'] }}</td>
                                <td class="border border-gray-400 px-4 py-2 text-left text-red-600">{{ $observation['value'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 m-2 rounded">
                @lang("Generate Report")
            </button>
        @endif
    </div>
</div>

@push('scripts')
{{-- <script src="https://unpkg.com/cornerstone-core"></script>
<script src="https://unpkg.com/cornerstone-web-image-loader"></script>
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
<script src="https://cdn.jsdelivr.net/npm/cornerstone-math@0.1.6"></script>
<script src="https://unpkg.com/cornerstone-tools"></script> --}}
<script>
    window.onload = async function() {
        const imageIds = createImageIdsAndCacheMetaData({
            StudyInstanceUID:
                '1.3.6.1.4.1.14519.5.2.1.7009.2403.334240657131972136850343327463',
            SeriesInstanceUID:
                '1.3.6.1.4.1.14519.5.2.1.7009.2403.226151125820845824875394858561',
            wadoRsRoot: 'https://d3t6nz73ql33tx.cloudfront.net/dicomweb',
        });

        const content = document.querySelector('.cornerstone-element-wrapper');
        const element = document.createElement('div');

        element.style.width = '500px';
        element.style.height = '500px';

        content.appendChild(element);

        const renderingEngineId = 'myRenderingEngine';
        const renderingEngine = new RenderingEngine(renderingEngineId);

        const viewportId = 'CT_AXIAL_STACK';

        const viewportInput = {
            viewportId,
            element,
            type: Enums.ViewportType.STACK,
        };

        renderingEngine.enableElement(viewportInput);

        const viewport = renderingEngine.getViewport(viewportId);

        viewport.setStack(imageIds, 60);

        viewport.render();


        // cornerstoneWebImageLoader.external.cornerstone = cornerstone;

        // // Setup tools
        // cornerstoneTools.init();

        // // Enable Element
        // const element = document.querySelector('.cornerstone-element');
        // cornerstone.enable(element);

        // // Add Tool
        // const LengthTool = cornerstoneTools['LengthTool'];
        // cornerstoneTools.addTool(LengthTool);
        // cornerstoneTools.addTool(cornerstoneTools.AngleTool);

        // // Set Tool Active
        // cornerstoneTools.setToolActive('Length', { mouseButtonMask: 1 });
        // cornerstoneTools.setToolActive('Angle', { mouseButtonMask: 2 });

        // Display an image
        // const imageId = 'http://elrazy.test/storage/test.png';
        // cornerstone.loadImage(image).then(function (image) {
        //     cornerstone.displayImage(element, image);
        // });
    }
</script>
@endpush

{{-- @script
<script>
    $wire.on('cornerstone-images-render', (event) => {
        const element = document.querySelector('.cornerstone-element');
        const imageId = event.images[0];
        console.log(imageId);
        cornerstone.loadImage(imageId).then(function (image) {
            cornerstone.displayImage(element, image);
        });
    })
</script>
@endscript --}}
