<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-gray-900 dark:text-gray-100 mb-4 text-2xl">
                {{ __("Welcome to AlRazi for X-Ray Clinic Diagnosis") }}
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @livewire('pages.image-diagnose.diagnosis-list')
            </div>
        </div>
    </div>
</x-app-layout>
