<x-app-layout>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Photo Enhancer</h2>
        @if(session('error'))
            <div class="mb-4 text-red-600">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('enhancer.process') }}" enctype="multipart/form-data" class="space-y-4" x-data="{
            dragging: false,
            previewUrl: null,
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
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
                <x-input-label for="image" :value="'Select Image to Enhance'" />
                <div x-bind:class="dragging ? 'border-2 border-indigo-500 bg-indigo-50' : 'border-2 border-dashed border-gray-300'" class="rounded p-4 text-center transition-colors cursor-pointer" @click="$refs.fileInput.click()">
                    <input id="image" name="image" type="file" accept="image/*" required class="hidden" x-ref="fileInput" @change="handleFileSelect" />
                    <span class="block text-gray-500">Drag & drop an image here, or <span class="text-indigo-600 underline">click to select</span></span>
                </div>
                @error('image')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <x-input-label :value="'Enhancements'" />
                <div class="flex flex-wrap gap-4 mt-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="sharpen" value="1" class="form-checkbox text-indigo-600 mr-2"> Sharpen
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="sharpen_human" value="1" class="form-checkbox text-indigo-600 mr-2"> Sharpen Human Only
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="color" value="1" class="form-checkbox text-indigo-600 mr-2"> Color Correction
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="denoise" value="1" class="form-checkbox text-indigo-600 mr-2"> Denoise
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="refocus" value="1" class="form-checkbox text-indigo-600 mr-2"> Refocus (Deblur)
                    </label>
                </div>
            </div>
            <div>
                <x-primary-button class="w-full justify-center">Enhance Photo</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout> 