<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="text-gray-900 dark:text-gray-100 mb-4">
                    {{ __("Welcome to AlRazi for X-Ray Clinic Diagnosis") }}
                </div>
                <a href="{{ route('diagnose') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __("Diagnose New X-Ray Image") }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
