<x-app-layout>
    <div class="max-w-2xl mx-auto mt-16 p-8 bg-white rounded shadow text-center">
        <h2 class="text-2xl font-bold mb-6">Upscaling Image...</h2>
        <div id="status-area">
            <div id="progress-bar-container" class="w-full bg-gray-200 rounded h-4 mb-6" style="display:none;">
                <div id="progress-bar" class="bg-indigo-500 h-4 rounded" style="width:0%"></div>
            </div>
            <div id="status-message">
                <div class="flex flex-col items-center justify-center">
                    <svg class="animate-spin h-10 w-10 text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <div class="text-gray-700">Your image is being upscaled. This may take a minute...</div>
                </div>
            </div>
        </div>
        <div id="preview-section" class="mt-8" style="display:none;">
            <h3 class="text-xl font-semibold mb-4">Upscaling Results</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="text-center">
                    <h4 class="font-medium mb-2">Original Image</h4>
                    <img id="original-preview" class="mx-auto rounded shadow max-h-64 mb-2">
                    <div id="original-details" class="text-sm text-gray-600"></div>
                </div>
                <div class="text-center">
                    <h4 class="font-medium mb-2">Upscaled Image</h4>
                    <img id="upscaled-preview" class="mx-auto rounded shadow max-h-64 mb-2">
                    <div id="upscaled-details" class="text-sm text-gray-600"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const id = @json($processed->id);
        const originalPath = @json($processed->path);
        const statusArea = document.getElementById('status-area');
        const progressBar = document.getElementById('progress-bar');
        const progressBarContainer = document.getElementById('progress-bar-container');
        const statusMessage = document.getElementById('status-message');
        const previewSection = document.getElementById('preview-section');

        function updateStatus() {
            fetch(`/api/upscale/status/${id}`)
                .then(res => res.json())
                .then(data => {
                    progressBarContainer.style.display = 'block';
                    progressBar.style.width = data.progress + '%';
                    
                    if (data.status === 'done') {
                        statusMessage.innerHTML = `<div class='mb-4 text-green-600 font-semibold'>Upscaling complete!</div><a href='/${data.result_path}' class='text-blue-600 underline' download>Download Upscaled Image</a>`;
                        progressBar.style.width = '100%';
                        showPreview(data.result_path);
                    } else if (data.status === 'failed') {
                        statusMessage.innerHTML = `<div class='mb-4 text-red-600 font-semibold'>Upscaling failed:</div><pre class='bg-red-100 text-red-800 p-2 rounded'>${data.error_message || 'Unknown error'}</pre>`;
                        progressBar.style.width = '100%';
                    } else if (data.status === 'processing') {
                        // Show progress percentage and detailed message
                        let progressMessage = data.progress_message || 'Upscaling in progress...';
                        if (data.progress > 0) {
                            progressMessage = `${data.progress_message || 'Upscaling in progress...'} (${data.progress}%)`;
                        }
                        
                        statusMessage.innerHTML = `<div class='flex flex-col items-center justify-center'><svg class='animate-spin h-10 w-10 text-indigo-500 mb-4' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'><circle class='opacity-25' cx='12' cy='12' r='10' stroke='currentColor' stroke-width='4'></circle><path class='opacity-75' fill='currentColor' d='M4 12a8 8 0 018-8v8z'></path></svg><div class='text-gray-700'>${progressMessage}</div></div>`;
                    } else {
                        statusMessage.innerHTML = `<div class='flex flex-col items-center justify-center'><svg class='animate-spin h-10 w-10 text-indigo-500 mb-4' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'><circle class='opacity-25' cx='12' cy='12' r='10' stroke='currentColor' stroke-width='4'></circle><path class='opacity-75' fill='currentColor' d='M4 12a8 8 0 018-8v8z'></path></svg><div class='text-gray-700'>Your image is in queue...</div></div>`;
                    }
                    
                    if (data.status !== 'done' && data.status !== 'failed') {
                        setTimeout(updateStatus, 2000);
                    }
                });
        }

        function showPreview(resultPath) {
            const originalPreview = document.getElementById('original-preview');
            const upscaledPreview = document.getElementById('upscaled-preview');
            const originalDetails = document.getElementById('original-details');
            const upscaledDetails = document.getElementById('upscaled-details');

            originalPreview.src = '/' + originalPath;
            upscaledPreview.src = '/' + resultPath;

            // Get image details
            getImageDetails(originalPath, originalDetails);
            getImageDetails(resultPath, upscaledDetails);

            previewSection.style.display = 'block';
        }

        function getImageDetails(imagePath, detailsElement) {
            const img = new Image();
            img.onload = function() {
                // Get file size via AJAX
                fetch(`/api/image-details?path=${encodeURIComponent(imagePath)}`)
                    .then(res => res.json())
                    .then(data => {
                        detailsElement.innerHTML = `${this.width} × ${this.height} px<br>${data.file_size}`;
                    })
                    .catch(() => {
                        detailsElement.innerHTML = `${this.width} × ${this.height} px`;
                    });
            };
            img.src = '/' + imagePath;
        }

        updateStatus();
    </script>
</x-app-layout> 