<div
    x-data="{ open: false, title: '', description: ''}"
    x-on:show-info.window="open = true; title = $event.detail.title; description = $event.detail.description"
>
    <div x-show="open" x-cloak>
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            <div class="relative bg-white rounded-lg w-1/3">
                <div class="flex flex-col items-center p-4 mt-4">
                    <h2 class="text-xl font-semibold mb-2" x-text="title"></h2>
                    <p class="text-sm text-gray-500 mt-2 p-4" x-text="description"></p>
                    <div class="flex justify-center mt-4">
                        <button @click="$dispatch('close-modal'); open = false;"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                            Ok
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
