<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Result: Upscaled Image</h2>
        <div x-data="{ slider: 50 }" x-ref="sliderbox" class="relative w-full max-w-xl mx-auto h-64 mb-6 select-none bg-gray-200 rounded shadow overflow-hidden">
            <!-- Upscaled image as background -->
            <img src="{{ asset($upscaled) }}" alt="Upscaled Image"
                 class="absolute inset-0 w-full h-full object-contain"
                 draggable="false" style="z-index: 10;">
            <!-- Original image on top, clipped -->
            <img src="{{ asset($original) }}" alt="Original Image"
                 class="absolute inset-0 w-full h-full object-contain"
                 draggable="false"
                 :style="'clip-path: inset(0 ' + (100 - slider) + '% 0 0); z-index: 20;'">
            <!-- Slider handle -->
            <div class="absolute inset-y-0" :style="'left: ' + slider + '%; transform: translateX(-50%); z-index: 30;'" style="width: 0;">
                <div class="w-1 bg-indigo-500 h-full"></div>
                <div class="w-6 h-6 bg-indigo-500 rounded-full border-4 border-white shadow-lg cursor-pointer absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"
                    x-on:mousedown.prevent="
                        const onMove = e => {
                            let rect = $refs.sliderbox.getBoundingClientRect();
                            let x = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
                            slider = Math.max(0, Math.min(100, ((x - rect.left) / rect.width) * 100));
                        };
                        const onUp = () => {
                            window.removeEventListener('mousemove', onMove);
                            window.removeEventListener('touchmove', onMove);
                            window.removeEventListener('mouseup', onUp);
                            window.removeEventListener('touchend', onUp);
                        };
                        window.addEventListener('mousemove', onMove);
                        window.addEventListener('touchmove', onMove);
                        window.addEventListener('mouseup', onUp);
                        window.addEventListener('touchend', onUp);
                    "
                    x-on:touchstart.prevent="
                        const onMove = e => {
                            let rect = $refs.sliderbox.getBoundingClientRect();
                            let x = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
                            slider = Math.max(0, Math.min(100, ((x - rect.left) / rect.width) * 100));
                        };
                        const onUp = () => {
                            window.removeEventListener('mousemove', onMove);
                            window.removeEventListener('touchmove', onMove);
                            window.removeEventListener('mouseup', onUp);
                            window.removeEventListener('touchend', onUp);
                        };
                        window.addEventListener('mousemove', onMove);
                        window.addEventListener('touchmove', onMove);
                        window.addEventListener('mouseup', onUp);
                        window.addEventListener('touchend', onUp);
                    "
                ></div>
            </div>
        </div>
        <div class="text-center text-sm text-gray-700 mb-4">
            Original: {{ $original_width }} x {{ $original_height }} px<br>
            Upscaled: {{ $upscaled_width }} x {{ $upscaled_height }} px
        </div>
        <div class="flex flex-col md:flex-row gap-4 justify-center items-center mt-4">
            <a href="{{ asset($upscaled) }}" download class="mb-2 md:mb-0">
                <x-primary-button>Download Upscaled Image</x-primary-button>
            </a>
            <a href="{{ route('upscale.form') }}">
                <x-primary-button>Upscale Another Image</x-primary-button>
            </a>
            <a href="{{ route('image.form') }}">
                <x-primary-button>Remove Background</x-primary-button>
            </a>
        </div>
        <div class="flex flex-col md:flex-row gap-6 items-center justify-center mb-6 mt-4">
            <div class="flex-1 text-center">
                <p class="mb-2 font-semibold">Original</p>
                <img src="{{ asset($original) }}" alt="Original Image" class="mx-auto rounded shadow max-h-64">
            </div>
            <div class="flex-1 text-center">
                <p class="mb-2 font-semibold">Upscaled</p>
                <img src="{{ asset($upscaled) }}" alt="Upscaled Image" class="mx-auto rounded shadow max-h-64">
            </div>
        </div>
    </div>
</x-app-layout> 