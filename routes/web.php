<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/dashboard', function () {
    return redirect()->route('landing');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Background removal routes
    Route::get('/bgremove', [\App\Http\Controllers\ImageController::class, 'showBgRemoveForm'])->name('bgremove.form');
    Route::post('/bgremove/process', [\App\Http\Controllers\ImageController::class, 'processBgRemove'])->name('bgremove.process');
    Route::post('/palette', [\App\Http\Controllers\ImageController::class, 'palette'])->name('image.palette');
    
    // Upscale routes
    Route::get('/upscale', [\App\Http\Controllers\ImageController::class, 'showUpscaleForm'])->name('upscale.form');
    Route::post('/upscale', [\App\Http\Controllers\ImageController::class, 'processUpscale'])->name('upscale.process');
    Route::get('/upscale/processing/{id}', [\App\Http\Controllers\ImageController::class, 'showUpscaleProcessing'])->name('upscale.processing');
    Route::get('/api/upscale/status/{id}', [\App\Http\Controllers\ImageController::class, 'upscaleStatus']);
    Route::get('/api/image-details', [\App\Http\Controllers\ImageController::class, 'imageDetails']);

    Route::get('/enhancer', [App\Http\Controllers\ImageController::class, 'showEnhancerForm'])->name('enhancer.form');
    Route::post('/enhancer', [App\Http\Controllers\ImageController::class, 'processEnhancer'])->name('enhancer.process');
});

require __DIR__.'/auth.php';
