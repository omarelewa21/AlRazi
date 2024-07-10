import './bootstrap';

document.addEventListener("alpine:init", () => {
    Alpine.data('diagnoseImage', () => ({
        images: [],
        allImagesHidden: [],
        init: function () {
            document.body.style.zoom = '75%';
        },
        zoom: function (event) {
            if(event.deltaY < 0) this.zoomIn();
            else if(event.deltaY > 0) this.zoomOut();
        },
        zoomIn: function () {
            const imagesContainer = this.$refs.imagesContainer;
            const multiply = imagesContainer.querySelectorAll('.image-container').length > 1 ? 2 : 1;
            const images = imagesContainer.querySelectorAll('img');
            images.forEach(image => {
                const newWidth = image.width * 1.1;
                const newHeight = image.height * 1.1;
                if(newWidth * multiply >= imagesContainer.clientWidth) return;
                image.width = newWidth;
                image.height = newHeight;
            });
        },
        zoomOut: function () {
            const images = this.$refs.imagesContainer.querySelectorAll('img');
            images.forEach(image => {
                image.width = image.width / 1.1;
                image.height = image.height / 1.1;
            });
        },
        toggleVisibility: function (index) {
            const firstKey = index.split('.')[0]
            this.images[index] = !this.images[index];
            this.allImagesHidden[firstKey] = Object.entries(this.images)
                .filter(([key, value]) => key.startsWith(firstKey))
                .every(([key, value]) => !value);
        },
        toggleAllVisibility: function (firstKey) {
            Object.entries(this.images)
                .filter(([image, value]) => image.startsWith(firstKey))
                .forEach(([key, value]) => this.images[key] = this.allImagesHidden[firstKey]);
            this.allImagesHidden[firstKey] = !this.allImagesHidden[firstKey];
        }
    }));
});
