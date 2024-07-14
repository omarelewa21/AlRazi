@foreach ($sourceImgs as $key => $sourceImg)
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
        class="relative @if($loop->last) mr-8 @endif"
        title="{{str()->headline($key)}}"
        x-inti="allImagesHidden['{{$key}}'] = false"
    >
        <button
            x-ref="button"
            x-on:click="toggle()"
            :aria-expanded="open"
            :aria-controls="$id('dropdown-button')"
            type="button"
            class="flex items-center gap-2 px-5 py-2 rounded-md shadow"
        >
            <template x-if="!allImagesHidden['{{$key}}']">
                <x-icons.eye-slash :h="20" :w="22.5"/>
            </template>
            <template x-if="allImagesHidden['{{$key}}']">
                <x-icons.eye :h="20" :w="22.5"/>
            </template>
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
                @click="toggleAllVisibility('{{$key}}')"
                class="flex items-start items-center gap-2 w-full first-of-type:rounded-t-md last-of-type:rounded-b-md px-4 py-2.5 text-left hover:bg-gray-50 text-lg disabled:text-gray-500"
            >
                <template x-if="!allImagesHidden['{{$key}}']">
                    <x-icons.eye-slash :h="20" :w="22.5"/>
                </template>
                <template x-if="allImagesHidden['{{$key}}']">
                    <x-icons.eye :h="20" :w="22.5"/>
                </template>
                @lang('All')
            </button>

            @foreach ($diagnoseImages[$key] as $k => $image)
                <button
                    @click="toggleVisibility('{{ "$key.$k" }}')"
                    class="w-full first-of-type:rounded-t-md last-of-type:rounded-b-md px-4 py-2.5 text-left hover:bg-gray-50 text-lg disabled:text-gray-500"
                >
                    <div x-show="images['{{ "$key.$k" }}']" class="flex items-start items-center gap-2">
                        <x-icons.eye-slash :h="20" :w="22.5"/>
                        {{ $k }}
                    </div>
                    <div x-show="!images['{{ "$key.$k" }}']" class="flex items-start items-center gap-2">
                        <x-icons.eye :h="20" :w="22.5"/>
                        {{ $k }}
                    </d>
                </button>
            @endforeach
        </div>
        <label class="absolute top-0 right-0 -mt-2 -mr-2 px-2 py-1 text-base font-bold rounded-full">{{ $loop->iteration }}</label>
    </div>
@endforeach
