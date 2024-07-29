<div
    x-data="{ show: false, message: '', type: 'success' }"
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2 sm:opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0 sm:opacity-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    class="fixed inset-0 flex items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end z-50"
    x-on:show-toast.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => { show = false; }, 5000);"
>
    <div
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2 sm:opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0 sm:opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        x-bind:class="{ 'bg-green-100 dark:bg-green-900': type === 'success', 'bg-red-100 dark:bg-red-900': type === 'error' }"
        class="max-w-sm w-full bg-green-100 dark:bg-green-900 shadow-lg rounded-lg pointer-events-auto flex items-center p-4 sm:p-6"
    >
        <div class="flex-shrink-0">
            <svg x-show="type === 'success'" class="h-6 w-6 text-green-400 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg x-show="type === 'error'" class="h-6 w-6 text-red-400 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        <div class="ml-3">
            <p x-text="message" class="text-sm font-medium text-green-800 dark:text-green-100"></p>
        </div>
    </div>
</div>
