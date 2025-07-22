<x-app-layout>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Background Remover</h2>
        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('bgremove.process') }}" enctype="multipart/form-data" class="space-y-4" x-data="{
            outputFormat: 'png',
            jpegBgColor: '#ffffff',
            palette: [],
            dragging: false,
            previewUrl: null,
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
            },
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.updatePalette(file);
                    this.showPreview(file);
                } else {
                    this.previewUrl = null;
                }
            },
            handleDrop(event) {
                this.dragging = false;
                event.preventDefault();
                if (event.dataTransfer.files.length > 0) {
                    const fileInput = $refs.fileInput;
                    fileInput.files = event.dataTransfer.files;
                    this.handleFileSelect({ target: fileInput });
                }
            },
            showPreview(file) {
                if (this.previewUrl) {
                    URL.revokeObjectURL(this.previewUrl);
                }
                this.previewUrl = URL.createObjectURL(file);
            }
        }"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="handleDrop"
        >
            @csrf
            <div x-show="previewUrl" class="mb-4 text-center">
                <img :src="previewUrl" alt="Preview" class="mx-auto rounded shadow max-h-48 inline-block">
                <button type="button" class="block mx-auto mt-2 text-xs text-red-500 hover:underline" @click="previewUrl = null; $refs.fileInput.value = null">Remove</button>
            </div>
            <div>
                <x-input-label for="image" :value="'Select Image'" />
                <div x-bind:class="dragging ? 'border-2 border-indigo-500 bg-indigo-50' : 'border-2 border-dashed border-gray-300'" class="rounded p-4 text-center transition-colors cursor-pointer" @click="$refs.fileInput.click()">
                    <input id="image" name="image" type="file" accept="image/*" required class="hidden" x-ref="fileInput" @change="handleFileSelect" />
                    <span class="block text-gray-500">Drag & drop an image here, or <span class="text-indigo-600 underline">click to select</span></span>
                </div>
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
                <x-primary-button class="w-full justify-center">Remove Background</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout> 