<x-app-layout>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Image Upscaler</h2>
        @if(session('error'))
            <div class="mb-4 text-red-600">{{ session('error') }}</div>
        @endif
        
        @if($image)
            <!-- Pre-filled image from background removal -->
            <div class="mb-4 text-center">
                <img src="{{ asset($image) }}" alt="Image to Upscale" class="mx-auto rounded shadow max-h-64">
                <p class="text-sm text-gray-600 mt-2">Image from background removal</p>
            </div>
            <form method="POST" action="{{ route('upscale.process') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="image" value="{{ $image }}">
                <div>
                    <x-input-label :value="'Upscale Factor'" />
                    <select name="scale" required class="block w-full mt-1 border-gray-300 rounded">
                        <option value="2">2x</option>
                        <option value="4">4x</option>
                    </select>
                </div>
                <div>
                    <x-primary-button class="w-full justify-center">Upscale Image</x-primary-button>
                </div>
            </form>
        @else
            <!-- Direct upload form -->
            <form method="POST" action="{{ route('upscale.process') }}" enctype="multipart/form-data" class="space-y-4" x-data="{
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
                    <x-input-label for="image" :value="'Select Image to Upscale'" />
                    <div x-bind:class="dragging ? 'border-2 border-indigo-500 bg-indigo-50' : 'border-2 border-dashed border-gray-300'" class="rounded p-4 text-center transition-colors cursor-pointer" @click="$refs.fileInput.click()">
                        <input id="image" name="image" type="file" accept="image/*" required class="hidden" x-ref="fileInput" @change="handleFileSelect" />
                        <span class="block text-gray-500">Drag & drop an image here, or <span class="text-indigo-600 underline">click to select</span></span>
                    </div>
                </div>
                <div>
                    <x-input-label :value="'Upscale Factor'" />
                    <select name="scale" required class="block w-full mt-1 border-gray-300 rounded">
                        <option value="2">2x</option>
                        <option value="4">4x</option>
                    </select>
                </div>
                <div>
                    <x-primary-button class="w-full justify-center">Upscale Image</x-primary-button>
                </div>
            </form>
        @endif
    </div>
</x-app-layout> 