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
                    @if ($this->allHidden())
                        <x-icons.eye />
                    @else
                        <x-icons.eye-slash />
                    @endif
                </button>


                <!-- Panel -->
                <div
                    x-ref="panel"
                    x-show="open"
                    x-transition.origin.top.left
                    x-on:click.outside="close($refs.button)"
                    :id="$id('dropdown-button')"
                    style="display: none;"
                    class="absolute left-0 mt-2 w-60 rounded-md bg-white shadow-md z-10"
                >
                    <button
                        wire:click="toggleAllVisibility"
                        class="flex items-start items-center gap-2 w-full first-of-type:rounded-t-md last-of-type:rounded-b-md px-4 py-2.5 text-left hover:bg-gray-50 text-lg disabled:text-gray-500"
                    >
                        @if ($this->allHidden())
                            <x-icons.eye :h="20" :w="22.5"/>
                        @else
                            <x-icons.eye-slash :h="20" :w="22.5"/>
                        @endif
                        @lang('All')
                    </button>

                    @foreach ($renderImages as  $key => $image)
                        <button
                            wire:click="toggleVisibility('{{ $key }}')"
                            class="flex items-start items-center gap-2 w-full first-of-type:rounded-t-md last-of-type:rounded-b-md px-4 py-2.5 text-left hover:bg-gray-50 text-lg disabled:text-gray-500"
                        >
                            @if ($image['visibility'])
                                <x-icons.eye-slash :h="20" :w="22.5"/>
                                {{ $key }}
                            @else
                                <x-icons.eye :h="20" :w="22.5"/>
                                {{ $key }}
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-5">
            @foreach ($renderImages as $image)
                @if ($image['visibility'])
                    <img src="{{ $image['url'] }}" alt="X-Ray Image" class="absolute">
                @endif
            @endforeach
        </div>
    </div>

    @include('livewire.pages.image-diagnose.observations-menu')
</div>
