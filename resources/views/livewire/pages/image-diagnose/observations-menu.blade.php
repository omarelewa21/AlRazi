<!-- Right Sidebar Table Information  -->
<div class="col-span-3 mr-2 bg-gray-200 flex flex-col" x-data="{
        blockCollapse: [],
        showOnlyThisBlock: function (block) {
            const isOpened = this.blockCollapse[block];
            Object.keys(this.blockCollapse).forEach(key => {
                this.blockCollapse[key] = false;
            });
            this.blockCollapse[block] = !isOpened;
        }
    }"
>
    <h3 class="text-xl bg-blue-500 mb-3 p-2 pl-2 font-semibold text-white dark:text-gray-200 leading-tight tracking-widest">
        @lang('Observations')
    </h3>
    @foreach ($observations as $title => $observation)
        <div class="overflow-auto mb-4 m-2 overflow-y-auto max-h-screen text-2xl font-sans border p-2 border-black divide-y-4">
            <div class="flex justify-between" :class="blockCollapse['{{$title}}'] && 'mb-4 border-b-2 border-black pb-2'" x-init="blockCollapse['{{$title}}'] = false">
                <h1> {{str()->headline($observation['observations']['view'])}} </h1>
                <button @click="showOnlyThisBlock('{{$title}}');">
                    <template x-if="blockCollapse['{{$title}}']">
                        <x-icons.minus />
                    </template>
                    <template x-if="!blockCollapse['{{$title}}']">
                        <x-icons.plus />
                    </template>
                </button>
            </div>

            <div x-show="blockCollapse['{{$title}}']" x-transition x-data="{collapse: []}">
                @foreach ($observation['observations'] as $obsTitle => $obsData)
                    @continue($obsTitle === 'view')

                    <div class="bg-white p-2 @if(!$loop->first) mt-2 @endif" x-init="collapse['{{$obsTitle}}'] = true">
                        <div class="flex justify-between">
                            <h4 class="text-2xl font-semibold text-blue-600 dark:text-gray-200 leading-tight mb-2">
                                {{ str()->headline($obsTitle) }}
                            </h4>
                            <button @click="collapse['{{$obsTitle}}'] = !collapse['{{$obsTitle}}']" class="text-red-600 dark:text-gray-200">
                                <template x-if="collapse['{{$obsTitle}}']">
                                    <x-icons.minus />
                                </template>
                                <template x-if="!collapse['{{$obsTitle}}']">
                                    <x-icons.plus />
                                </template>
                            </button>
                        </div>

                        @if(\Illuminate\Support\Arr::first($obsData, fn($value) => is_array($value)))
                            @foreach ($obsData as $obsTitle2 => $obsData2)
                                <div class="bg-white mt-2 @if($loop->first) mt-4 @endif" x-show="collapse['{{$obsTitle}}']" x-transition>
                                    <div class="flex justify-between">
                                        <h4 class="text-xl font-semibold text-blue-600 dark:text-gray-200 leading-tight mb-2">
                                            {{ str()->headline($obsTitle2) }}
                                        </h4>
                                    </div>

                                    <table class="table-auto w-full transform transition duration-500 ease-in-out mb-2">
                                        <thead class="text-lg">
                                            <tr>
                                                <th class="border border-gray-400 px-4 py-2 text-left">@lang('Variable')</th>
                                                <th class="border border-gray-400 px-4 py-2 text-left">@lang('Observation')</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-base">
                                            @foreach ($obsData2 as $key => $observation)
                                                <tr class="font-sans">
                                                    <td class="border border-gray-400 px-4 py-2 text-left">{{ str()->headline($key) }}</td>
                                                    <td class="border border-gray-400 px-4 py-2 text-left text-red-600">{!! $observation !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        @else
                            <table class="table-auto w-full transform transition duration-500 ease-in-out mb-2" x-show="collapse['{{$obsTitle}}']" x-transition>
                                <thead>
                                    <tr class="text-lg">
                                        <th class="border border-gray-400 px-4 py-2 text-left">@lang('Variable')</th>
                                        <th class="border border-gray-400 px-4 py-2 text-left">@lang('Observation')</th>
                                    </tr>
                                </thead>
                                <tbody class="text-base">
                                    @foreach ($obsData as $key => $observation)
                                        <tr class="font-sans">
                                            <td class="border border-gray-400 px-4 py-2 text-left">{{ str()->headline($key) }}</td>
                                            <td class="border border-gray-400 px-4 py-2 text-left text-red-600">{!! $observation !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if(!empty($observations))
        <button wire:click='showReport' class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 m-2 rounded">
            @lang("Generate Report")
        </button>
    @endif
</div>
