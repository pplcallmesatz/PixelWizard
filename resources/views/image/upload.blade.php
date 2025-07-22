<x-app-layout>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Image Background Remover & Upscaler</h2>
        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('image.process') }}" enctype="multipart/form-data" class="space-y-4" x-data="{
            outputFormat: 'png',
            jpegBgColor: '#ffffff',
            palette: [],
            updatePalette(file) {
                if (!file) return;
                const formData = new FormData();
                formData.append('image', file);
                fetch('{{ route('image.palette') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            alert('Palette fetch failed: ' + JSON.stringify(err));
                            console.error('Palette fetch failed:', err);
                            return null;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.palette) {
                        this.palette = data.palette;
                        if (this.palette.length > 0) {
                            this.jpegBgColor = this.palette[0];
                        }
                    } else if (data) {
                        alert('Palette fetch returned no palette.');
                        console.error('Palette fetch returned:', data);
                    }
                })
                .catch(error => {
                    alert('Palette fetch error: ' + error);
                    console.error('Palette fetch error:', error);
                });
            }
        }">
            @csrf
            <div>
                <x-input-label for="image" :value="'Select Image'" />
                <input id="image" name="image" type="file" accept="image/*" required class="block w-full mt-1 border-gray-300 rounded"
                    x-on:change="updatePalette($event.target.files[0])" />
            </div>
            <div>
                <x-input-label for="action" :value="'Action'" />
                <select id="action" name="action" required class="block w-full mt-1 border-gray-300 rounded">
                    <option value="remove_bg">Remove Background</option>
                    <option value="upscale">Upscale Image</option>
                </select>
            </div>
            <div>
                <x-input-label :value="'Output Format'" />
                <div class="flex items-center gap-4 mt-1">
                    <label class="inline-flex items-center">
                        <input type="radio" name="output_format" value="png" x-model="outputFormat" checked class="form-radio text-indigo-600">
                        <span class="ml-2">PNG (transparent background)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="output_format" value="jpeg" x-model="outputFormat" class="form-radio text-indigo-600">
                        <span class="ml-2">JPEG (color background)</span>
                    </label>
                </div>
            </div>
            <div x-show="outputFormat === 'jpeg'" class="transition-all">
                <x-input-label :value="'JPEG Background Color'" />
                <div class="flex items-center gap-4 mt-1">
                    <input type="color" name="jpeg_bgcolor" x-model="jpegBgColor" class="w-10 h-10 p-0 border-0 cursor-pointer" />
                    <template x-for="color in palette" :key="color">
                        <button type="button" class="w-8 h-8 rounded-full border-2 border-gray-300 focus:border-indigo-500" :style="'background:' + color" x-on:click="jpegBgColor = color"></button>
                    </template>
                </div>
                <input type="hidden" name="jpeg_bgcolor" :value="jpegBgColor">
            </div>
            <div>
                <x-primary-button class="w-full justify-center">Process Image</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout> 