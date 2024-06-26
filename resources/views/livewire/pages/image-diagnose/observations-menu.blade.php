<!-- Right Sidebar Table Information  -->
<div class="col-span-3 mr-2 bg-gray-200 flex flex-col overflow-y-auto max-h-screen">
    <h3 class="text-xl bg-blue-500 mb-3 p-2 pl-2 font-semibold text-white dark:text-gray-200 leading-tight tracking-widest">
        @lang('Observations')
    </h3>
    @if (!empty($observations))
        <div class="overflow-auto mb-4 m-2" x-data="{collapse: []}">
            @foreach ($observations as $obsTitle => $obsData)
                <div class="bg-white p-2 @if(!$loop->first) mt-2 @endif" x-init="collapse['{{$obsTitle}}'] = true">
                    <div class="flex justify-between">
                        <h4 class="text-xl font-semibold text-blue-600 dark:text-gray-200 leading-tight mb-2">
                            {{ $obsTitle }}
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

                    <table class="table-auto w-full transform transition duration-500 ease-in-out mb-2" x-show="collapse['{{$obsTitle}}']" x-transition>
                        <thead>
                            <tr>
                                <th class="border border-gray-400 px-4 py-2 text-left">@lang('Variable')</th>
                                <th class="border border-gray-400 px-4 py-2 text-left">@lang('Observation')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($obsData as $key => $observation)
                                <tr class="font-sans">
                                    <td class="border border-gray-400 px-4 py-2 text-left">{{ $key }}</td>
                                    <td class="border border-gray-400 px-4 py-2 text-left text-red-600">{!! $observation !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>

        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 m-2 rounded">
            @lang("Generate Report")
        </button>
    @endif
</div>
