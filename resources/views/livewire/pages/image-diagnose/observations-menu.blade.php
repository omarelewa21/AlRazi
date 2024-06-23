<!-- Right Sidebar Table Information  -->
<div class="col-span-3 mr-2 bg-gray-200 flex flex-col">
    <h3 class="text-xl bg-blue-500 mb-3 p-2 pl-2 font-semibold text-white dark:text-gray-200 leading-tight tracking-widest">
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
