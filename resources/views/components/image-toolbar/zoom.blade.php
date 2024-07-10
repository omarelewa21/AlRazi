<div class="flex items-center">
    <button
        @click="zoomIn()"
        class="flex items-center gap-2 px-5 py-2 rounded-md shadow"
    >
        <x-icons.zoom-in />
    </button>

    <button
        @click="zoomOut()"
        class="flex items-center gap-2 px-5 py-2 rounded-md shadow"
    >
        <x-icons.zoom-out />
    </button>
</div>
