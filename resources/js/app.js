import './bootstrap';

document.addEventListener("alpine:init", () => {
    Alpine.data('diagnoseImage', () => ({
        images: [],
        allImagesHidden: [],
        tools: [],
        svg: null,
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
            this.resizeSvg(images[0]);
        },
        zoomOut: function () {
            const images = this.$refs.imagesContainer.querySelectorAll('img');
            images.forEach(image => {
                image.width = image.width / 1.1;
                image.height = image.height / 1.1;
            });
            this.resizeSvg(images[0]);
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
        },
        resizeSvg: function (image) {
            const svgs = this.$refs.imagesContainer.querySelectorAll('svg');
            this.resizeSvgElements(image.width, image.height);
            svgs.forEach(svg => {
                svg.setAttribute('width', image.width);
                svg.setAttribute('height', image.height);
            });
        },
        resizeSvgElements: function (newWidth, newHeight) {
            this.resizeSvgLines(newWidth, newHeight);
        },
        resizeSvgLines: function (newWidth, newHeight) {
            const lines = this.$refs.imagesContainer.querySelectorAll('line');
            lines.forEach(line => {
                line.setAttribute('x1', line.getAttribute('x1') * newWidth / line.closest('svg').getAttribute('width'));
                line.setAttribute('x2', line.getAttribute('x2') * newWidth / line.closest('svg').getAttribute('width'));
                line.setAttribute('y1', line.getAttribute('y1') * newHeight / line.closest('svg').getAttribute('height'));
                line.setAttribute('y2', line.getAttribute('y2') * newHeight / line.closest('svg').getAttribute('height'));
            });
            this.resizeSvgTexts(newWidth, newHeight);
        },
        resizeSvgTexts: function (newWidth, newHeight) {
            const texts = this.$refs.imagesContainer.querySelectorAll('text');
            texts.forEach(text => {
                text.setAttribute('x', text.getAttribute('x') * newWidth / text.closest('svg').getAttribute('width'));
                text.setAttribute('y', text.getAttribute('y') * newHeight / text.closest('svg').getAttribute('height'));
            });
        },
        addSvgToTools: function(index) {
            this.tools[index] = {
                'length': {
                    isActive: false,
                    isStarted: false,
                },
            };
        },
        startLineDraw: function (event, index) {
            if(!this.tools[index].length.isActive) return;
            if(this.tools[index].length.isStarted) return this.endLineDraw(event, index);

            this.tools[index].length.isStarted = true;
            this.svg = event.target.closest('svg');
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', (event.offsetX / 0.75));
            line.setAttribute('y1', event.offsetY / 0.75);
            line.setAttribute('x2', event.offsetX / 0.75);
            line.setAttribute('y2', event.offsetY / 0.75);
            line.setAttribute('stroke', 'green');
            line.setAttribute('stroke-width', 4);
            this.svg.appendChild(line);
        },
        continueLineDraw: function (event, index) {
            if(!this.tools[index].length.isStarted) return;
            const line = this.svg.querySelector('line:last-child');
            line.setAttribute('x2', event.offsetX / 0.75);
            line.setAttribute('y2', event.offsetY / 0.75);
        },
        endLineDraw: function (event, index) {
            if(!this.tools[index].length.isStarted) return;
            this.tools[index].length.isStarted = false;
            this.diplayDistance(event, index);
            this.svg = null;
        },
        diplayDistance: function (event, index) {
            const line = this.svg.querySelector('line:last-child');
            const distance = Math.sqrt(Math.pow(line.getAttribute('x2') - line.getAttribute('x1'), 2) + Math.pow(line.getAttribute('y2') - line.getAttribute('y1'), 2));
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', (parseInt(line.getAttribute('x1')) + parseInt(line.getAttribute('x2'))) / 2);
            text.setAttribute('y', (parseInt(line.getAttribute('y1')) + parseInt(line.getAttribute('y2'))) / 2);
            const backgroundColor = window.getComputedStyle(this.svg).getPropertyValue('background-color');
            const isLightBackground = isLightColor(backgroundColor);
            text.setAttribute('fill', isLightBackground ? '#1A5319' : '#FFFFFF');

            function isLightColor(color) {
                const rgb = color.match(/\d+/g);
                const brightness = (rgb[0] * 299 + rgb[1] * 587 + rgb[2] * 114) / 1000;
                return brightness > 125;
            }
            text.setAttribute('font-weight', 'bold');
            text.setAttribute('font-size', '16px');
            const roundedDistance = Math.round(distance * 100) / 100;
            text.textContent = roundedDistance + ' mm';
            this.svg.appendChild(text);
            // this.addDashLine(line, text);
        },
        addDashLine: function (line, text) {
            const dashedLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            dashedLine.setAttribute('x1', line.getAttribute('x1'));
            dashedLine.setAttribute('y1', line.getAttribute('y1'));
            dashedLine.setAttribute('x2', text.getAttribute('x'));
            dashedLine.setAttribute('y2', text.getAttribute('y') - 10);
            dashedLine.setAttribute('stroke', 'green');
            dashedLine.setAttribute('stroke-dasharray', '5,5');
            this.svg.appendChild(dashedLine);
        },
        toggleLengthTool: function (el) {
            const currentState = this.tools[0].length.isActive;
            this.tools.forEach(tool => {
                tool.length.isActive = !currentState;
            });
            if(currentState) {
                el.classList.remove('outline', 'outline-2', 'outline-blue-500')
            } else {
                el.classList.add('outline', 'outline-2', 'outline-blue-500')
            }
        }
    }));
});
