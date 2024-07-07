<div class="flex items-center"
    x-data="{
        zoomIn: function () {
            const images = document.querySelectorAll('.box-1-image');
            const imageContainer = document.getElementById('image-container');
            const maxWidth = imageContainer.clientWidth;
            images.forEach(image => {
                const currentWidth = image.width;
                const currentHeight = image.height;

                if(currentWidth * 1.1 >= maxWidth) return;

                image.width = currentWidth * 1.1;
                image.height = currentHeight * 1.1;
                if (currentWidth > maxWidth) {
                    image.width = maxWidth;
                    image.height = currentHeight * (maxWidth / currentWidth);
                }
            });
        },

        zoomOut: function () {
            const images = document.querySelectorAll('.box-1-image');
            images.forEach(image => {
                const currentWidth = image.width;
                const currentHeight = image.height;

                image.width = currentWidth / 1.1;
                image.height = currentHeight / 1.1;
            });
        }
    }"
>
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
