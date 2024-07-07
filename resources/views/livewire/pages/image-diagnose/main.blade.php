<div class="grid grid-cols-12 font-serif h-full min-h-screen">
    @include('livewire.pages.image-diagnose.patient-info-menu')
    <!-- Image Display -->
    <div class="col-span-8 overflow-hidden">
        <!-- Toolbar -->
        <div class="flex justify-center bg-gray-200 p-2 min-h-12">
            @if(!empty($diagnoseImages))
                @include('components.image-toolbar.visibility-toggle')
            @endif

            @if ($sourceImg)
                @include('components.image-toolbar.zoom')
            @endif
        </div>

        <div class="flex justify-center mt-5" id="image-container" x-ref="image-container" x-data="{imgWidth: 0, imgHeight: 0}">
            @if ($sourceImg)
                <img src="{{ $sourceImg['url'] }}" alt="X-Ray Image" class="relative box-1-image" id="source-image" x-ref="source-image"
                    x-init="imgWidth = $el.width; imgHeight = $el.height;"
                >
            @endif
            @foreach ($diagnoseImages as $image)
                @if ($image['visibility'])
                    <img src="{{ $image['url'] }}" alt="X-Ray Image" class="absolute box-1-image">
                @endif
            @endforeach
            <svg id="annotation-svg" x-ref="annotation-svg" class="absolute" :style="{width: imgWidth, height: imgHeight}"></svg>
        </div>
    </div>

    @include('livewire.pages.image-diagnose.observations-menu')
    <div wire:loading wire:target="processDiagnosis, file, showReport">
        <x-loading />
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const imageContainer = document.getElementById('image-container');
        const image = document.getElementById('source-image');
        const svg = document.getElementById('annotation-svg');
        let points = [];
        let isDragging = false;

        svg.addEventListener('click', function (event) {
            if (isDragging) return; // Prevent drawing a point if dragging

            const rect = image.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            points.push({ x, y });
            console.log(points);

            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', x);
            circle.setAttribute('cy', y);
            circle.setAttribute('r', 4);
            circle.setAttribute('fill', 'red');
            svg.appendChild(circle);

            if (points.length === 2) {
                drawLine(points[0], points[1]);
                displayDistance(points[0], points[1]);
                points = [];
            }
        });

        function drawLine(point1, point2) {
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', point1.x);
            line.setAttribute('y1', point1.y);
            line.setAttribute('x2', point2.x);
            line.setAttribute('y2', point2.y);
            line.setAttribute('stroke', 'red');
            line.setAttribute('stroke-width', 2);
            svg.appendChild(line);
        }

        function displayDistance(point1, point2) {
            const distance = Math.sqrt((point2.x - point1.x) ** 2 + (point2.y - point1.y) ** 2);
            const midX = (point1.x + point2.x) / 2;
            const midY = (point1.y + point2.y) / 2;
            const offsetX = 10; // Adjust this value to move the label further horizontally
            const offsetY = -10; // Adjust this value to move the label further vertically

            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', midX + offsetX);
            text.setAttribute('y', midY + offsetY);
            text.setAttribute('fill', 'green');
            text.classList.add('draggable');
            text.textContent = `${distance.toFixed(2)} px`;
            svg.appendChild(text);

            const dashedLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            dashedLine.setAttribute('x1', midX);
            dashedLine.setAttribute('y1', midY);
            dashedLine.setAttribute('x2', midX + offsetX);
            dashedLine.setAttribute('y2', midY + offsetY);
            dashedLine.setAttribute('stroke', 'green');
            dashedLine.setAttribute('stroke-width', 1);
            dashedLine.setAttribute('stroke-dasharray', '4,2'); // Creates a dashed line
            svg.appendChild(dashedLine);

            makeDraggable(text);
        }

        function makeDraggable(element) {
            element.addEventListener('mousedown', startDrag);
        }

        function startDrag(event) {
            isDragging = true;
            selectedElement = event.target;
            offset = getMousePosition(event);
            offset.x -= parseFloat(selectedElement.getAttribute('x'));
            offset.y -= parseFloat(selectedElement.getAttribute('y'));
            selectedElement.classList.add('dragging');
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', endDrag);
        }

        function drag(event) {
            const coord = getMousePosition(event);
            selectedElement.setAttribute('x', coord.x - offset.x);
            selectedElement.setAttribute('y', coord.y - offset.y);

            const dashedLine = selectedElement.nextElementSibling;
            if (dashedLine && dashedLine.tagName === 'line') {
                dashedLine.setAttribute('x2', coord.x - offset.x);
                dashedLine.setAttribute('y2', coord.y - offset.y);
            }
        }

        function endDrag() {
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', endDrag);
            selectedElement.classList.remove('dragging');
            selectedElement = null;
            offset = null;
            setTimeout(() => {
                isDragging = false;
            }, 1);
        }

        function getMousePosition(event) {
            const svgRect = svg.getBoundingClientRect();
            return {
                x: event.clientX - svgRect.left,
                y: event.clientY - svgRect.top
            };
        }

        function zoomIn() {
            const images = document.querySelectorAll('.box-1-image');
            const imageContainer = document.getElementById('image-container');
            const maxWidth = imageContainer.clientWidth;
            images.forEach(image => {
                const currentWidth = image.width;
                const currentHeight = image.height;
                image.width = currentWidth * 1.1;
                image.height = currentHeight * 1.1;
                if (currentWidth > maxWidth) {
                    image.width = maxWidth;
                    image.height = currentHeight * (maxWidth / currentWidth);
                }
            });
        }

        function zoomOut() {
            const images = document.querySelectorAll('.box-1-image');
            images.forEach(image => {
                const currentWidth = image.width;
                const currentHeight = image.height;

                image.width = currentWidth / 1.1;
                image.height = currentHeight / 1.1;
            });
        }
    });
</script>
