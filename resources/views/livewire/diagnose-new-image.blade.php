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
                <label for="image" class="font-semibold block required">@lang('Upload X-Ray Image')</label>
                <input type="file" wire:model="image" required class="rounded w-full">
                @error('image') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mt-3">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    @lang('Process Diagnosis')
                </button>
            </div>
        </form>
    </div>

    <!-- Image Display -->
    <div class="col-span-6">

    </div>

    <!-- Right Sidebar Table Information  -->
    <div class="col-span-2 mr-2 bg-gray-200 flex flex-col">
        <h3 class="text-lg bg-blue-500 mb-3 p-1 pl-2 font-semibold text-white dark:text-gray-200 leading-tight">
            @lang('Observations')
        </h3>
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
    </div>
</div>
</table>
