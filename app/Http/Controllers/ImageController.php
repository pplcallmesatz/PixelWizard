<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use ColorThief\ColorThief;
use App\Models\ProcessedImage;
use App\Jobs\UpscaleImageJob;

class ImageController extends Controller
{
    // Show the background removal form
    public function showBgRemoveForm(Request $request)
    {
        $palette = [];
        $imagePath = null;
        if ($request->has('uploaded')) {
            $imagePath = storage_path('app/public/uploads/' . $request->query('uploaded'));
            if (file_exists($imagePath)) {
                $palette = $this->getPastelPalette($imagePath, 5);
            }
        }
        return view('bgremove.form', [
            'palette' => $palette,
            'uploaded' => $request->query('uploaded'),
        ]);
    }

    private function getPastelPalette($imagePath, $count = 5)
    {
        $palette = ColorThief::getPalette($imagePath, $count);
        // Convert to pastel
        $pastel = array_map(function($rgb) {
            $r = intval(($rgb[0] + 255) / 2);
            $g = intval(($rgb[1] + 255) / 2);
            $b = intval(($rgb[2] + 255) / 2);
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        }, $palette);
        return $pastel;
    }

    // Handle background removal processing
    public function processBgRemove(Request $request)
    {
        set_time_limit(300); // Increase max execution time to 5 minutes
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);

        $file = $request->file('image');
        $outputFormat = $request->input('output_format', 'png');
        $jpegBgColor = $request->input('jpeg_bgcolor', '#ffffff');
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $inputPath = $file->storeAs('uploads', $filename, 'public');
        $inputFullPath = storage_path('app/public/' . $inputPath);

        $outputFilename = 'processed_' . pathinfo($filename, PATHINFO_FILENAME) . '.' . $outputFormat;
        $outputPath = 'processed/' . $outputFilename;
        $outputFullPath = storage_path('app/public/' . $outputPath);

        // Ensure processed directory exists
        Storage::disk('public')->makeDirectory('processed');

        // Always run background removal script
        $script = base_path('python/remove_bg.py');
        $args = ['python3', $script, $inputFullPath, $outputFullPath, $outputFormat];
        if ($outputFormat === 'jpeg') {
            $args[] = $jpegBgColor;
        }
        $process = new Process($args);
        $process->run();

        if (!$process->isSuccessful()) {
            return back()->withErrors(['error' => 'Image processing failed: ' . $process->getErrorOutput()]);
        }

        // Get processed image details
        $processedWidth = null;
        $processedHeight = null;
        $processedSize = null;
        if (file_exists($outputFullPath)) {
            $imageInfo = getimagesize($outputFullPath);
            if ($imageInfo) {
                $processedWidth = $imageInfo[0];
                $processedHeight = $imageInfo[1];
            }
            $processedSize = filesize($outputFullPath);
        }

        return view('bgremove.result', [
            'original' => 'storage/' . $inputPath,
            'processed' => 'storage/' . $outputPath,
            'processed_width' => $processedWidth,
            'processed_height' => $processedHeight,
            'processed_size' => $processedSize,
        ]);
    }

    public function palette(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);
        if ($validator->fails()) {
            \Log::error('Palette validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $file = $request->file('image');
        $tmpPath = $file->storeAs('palette_tmp', uniqid() . '.' . $file->getClientOriginalExtension(), 'public');
        $fullPath = storage_path('app/public/' . $tmpPath);
        $palette = $this->getPastelPalette($fullPath, 5);
        // Clean up temp file
        @unlink($fullPath);
        return response()->json(['palette' => $palette]);
    }

    public function showUpscaleForm(Request $request)
    {
        $image = $request->query('image');
        $palette = [];
        
        // If image is provided, generate palette for it
        if ($image) {
            $imagePath = public_path($image);
            if (file_exists($imagePath)) {
                $palette = $this->getPastelPalette($imagePath, 5);
            }
        }
        
        return view('upscale.form', [
            'image' => $image,
            'palette' => $palette,
        ]);
    }

    public function processUpscale(Request $request)
    {
        $request->validate([
            'image' => 'required',
            'scale' => 'required|integer|min:2|max:5',
        ]);
        
        $scale = (int) $request->input('scale');
        
        // Handle both file upload and existing image path
        if ($request->hasFile('image')) {
            // File upload
            $file = $request->file('image');
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $inputPath = $file->storeAs('uploads', $filename, 'public');
            $imagePath = 'storage/' . $inputPath;
        } else {
            // Existing image path
            $imagePath = $request->input('image');
        }
        
        $inputFullPath = public_path($imagePath);
        if (!file_exists($inputFullPath)) {
            return back()->with('error', 'Image file not found.');
        }
        
        $outputFilename = 'upscaled_' . uniqid() . '_' . basename($imagePath);
        $outputPath = 'storage/processed/' . $outputFilename;
        
        // Create DB record
        $processed = ProcessedImage::create([
            'path' => $imagePath,
            'status' => 'pending',
            'result_path' => null,
            'error_message' => null,
        ]);
        
        // Dispatch job
        UpscaleImageJob::dispatch($processed->id, $scale, $outputPath);
        
        // Redirect to processing view
        return redirect()->route('upscale.processing', ['id' => $processed->id]);
    }

    public function showUpscaleProcessing($id)
    {
        $processed = \App\Models\ProcessedImage::findOrFail($id);
        return view('upscale.processing', ['processed' => $processed]);
    }

    public function upscaleStatus($id)
    {
        $processed = \App\Models\ProcessedImage::findOrFail($id);
        
        // Return real progress from database
        $progress = $processed->progress ?? 0;
        
        // For pending status, keep progress at 0
        if ($processed->status === 'pending') {
            $progress = 0;
        }
        
        return response()->json([
            'status' => $processed->status,
            'result_path' => $processed->result_path,
            'error_message' => $processed->error_message,
            'progress' => $progress,
            'progress_message' => $processed->progress_message ?? $this->getProgressMessage($progress),
        ]);
    }
    
    private function getProgressMessage($progress)
    {
        if ($progress <= 5) return 'Starting upscaling process...';
        if ($progress <= 10) return 'Loading image...';
        if ($progress <= 15) return 'Image loaded successfully';
        if ($progress <= 20) return 'Preparing model...';
        if ($progress <= 25) return 'Model configuration ready';
        if ($progress <= 30) return 'Initializing upscaler...';
        if ($progress <= 35) return 'Upscaler initialized';
        if ($progress <= 40) return 'Starting upscaling process...';
        if ($progress <= 50) return 'Processing image...';
        if ($progress <= 70) return 'Upscaling completed...';
        if ($progress <= 80) return 'Saving image...';
        if ($progress <= 90) return 'Image saved successfully';
        if ($progress <= 100) return 'Upscaling process completed';
        return 'Processing...';
    }

    public function imageDetails(Request $request)
    {
        $path = $request->query('path');
        $fullPath = public_path($path);
        
        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        $fileSize = filesize($fullPath);
        $fileSizeMB = number_format($fileSize / 1048576, 2);
        
        $imageInfo = getimagesize($fullPath);
        $width = $imageInfo ? $imageInfo[0] : null;
        $height = $imageInfo ? $imageInfo[1] : null;
        
        return response()->json([
            'file_size' => $fileSizeMB . ' MB',
            'width' => $width,
            'height' => $height,
        ]);
    }

    // Show the photo enhancer form
    public function showEnhancerForm(Request $request)
    {
        $imagePath = $request->query('image');
        return view('enhancer.form', [
            'image' => $imagePath,
        ]);
    }

    // Handle photo enhancement processing
    public function processEnhancer(Request $request)
    {
        set_time_limit(300);
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'sharpen' => 'nullable|boolean',
            'sharpen_human' => 'nullable|boolean',
            'color' => 'nullable|boolean',
            'denoise' => 'nullable|boolean',
            'refocus' => 'nullable|boolean',
        ]);

        $file = $request->file('image');
        $filename = \Illuminate\Support\Str::random(20) . '.' . $file->getClientOriginalExtension();
        $inputPath = $file->storeAs('uploads', $filename, 'public');
        $inputFullPath = storage_path('app/public/' . $inputPath);

        $outputFilename = 'enhanced_' . pathinfo($filename, PATHINFO_FILENAME) . '.' . $file->getClientOriginalExtension();
        $outputPath = 'processed/' . $outputFilename;
        $outputFullPath = storage_path('app/public/' . $outputPath);

        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('processed');

        $script = base_path('python/enhance.py');
        $args = ['python3', $script, '--input', $inputFullPath, '--output', $outputFullPath];
        if ($request->boolean('sharpen')) {
            $args[] = '--sharpen';
        }
        if ($request->boolean('sharpen_human')) {
            $args[] = '--sharpen-human';
        }
        if ($request->boolean('color')) {
            $args[] = '--color';
        }
        if ($request->boolean('denoise')) {
            $args[] = '--denoise';
        }
        if ($request->boolean('refocus')) {
            $args[] = '--refocus';
        }
        $process = new \Symfony\Component\Process\Process($args);
        $process->run();

        if (!$process->isSuccessful()) {
            return back()->withErrors(['error' => 'Photo enhancement failed: ' . $process->getErrorOutput()]);
        }

        return view('enhancer.result', [
            'original' => 'storage/' . $inputPath,
            'enhanced' => 'storage/' . $outputPath,
        ]);
    }
} 