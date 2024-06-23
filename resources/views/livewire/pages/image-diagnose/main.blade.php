<div class="grid grid-cols-10 font-serif h-screen">
    @include('livewire.pages.image-diagnose.patient-info-menu')

    <!-- Image Display -->
    <div class="col-span-5 overflow-hidden">
        <!-- Image Toolbar -->
        <div class="flex justify-center bg-gray-200 p-2">
            <div
                x-data="{
                    open: false,
                    toggle() {
                        if (this.open) {
                            return this.close()
                        }

                        this.$refs.button.focus()

                        this.open = true
                    },
                    close(focusAfter) {
                        if (! this.open) return

                        this.open = false

                        focusAfter && focusAfter.focus()
                    }
                }"
                x-on:keydown.escape.prevent.stop="close($refs.button)"
                x-on:focusin.window="! $refs.panel.contains($event.target) && close()"
                x-id="['dropdown-button']"
                class="relative"
            >
                <button
                    x-ref="button"
                    x-on:click="toggle()"
                    :aria-expanded="open"
                    :aria-controls="$id('dropdown-button')"
                    type="button"
                    class="flex items-center gap-2 px-5 py-2 rounded-md shadow"
                >
                    <x-icons.eye />
                </button>


                <!-- Panel -->
                <div
                    x-ref="panel"
                    x-show="open"
                    x-transition.origin.top.left
                    x-on:click.outside="close($refs.button)"
                    :id="$id('dropdown-button')"
                    style="display: none;"
                    class="absolute left-0 mt-2 w-40 rounded-md bg-white shadow-md z-10"
                >
                    <a href="#" class="flex items-center gap-2 w-full first-of-type:rounded-t-md last-of-type:rounded-b-md px-4 py-2.5 text-left text-sm hover:bg-gray-50 disabled:text-gray-500">
                        New Task
                    </a>

                    <a href="#" class="flex items-center gap-2 w-full first-of-type:rounded-t-md last-of-type:rounded-b-md px-4 py-2.5 text-left text-sm hover:bg-gray-50 disabled:text-gray-500">
                        Edit Task
                    </a>

                    <a href="#" class="flex items-center gap-2 w-full first-of-type:rounded-t-md last-of-type:rounded-b-md px-4 py-2.5 text-left text-sm hover:bg-gray-50 disabled:text-gray-500">
                        <span class="text-red-600">Delete Task</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-5">
            @foreach ($renderImages as $image)
                <img src="{{ $image }}" alt="X-Ray Image" class="absolute">
            @endforeach
        </div>
    </div>

    @include('livewire.pages.image-diagnose.observations-menu')
</div>
