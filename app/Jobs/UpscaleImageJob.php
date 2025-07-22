<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ProcessedImage; // Adjust if your model is named differently
use Illuminate\Support\Facades\Log;

class UpscaleImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $imageId;
    protected $scale;
    protected $outputPath;

    /**
     * Create a new job instance.
     */
    public function __construct($imageId, $scale, $outputPath)
    {
        $this->imageId = $imageId;
        $this->scale = $scale;
        $this->outputPath = $outputPath;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $image = ProcessedImage::find($this->imageId);
            if (!$image) {
                Log::error('UpscaleImageJob: Image not found', ['id' => $this->imageId]);
                return;
            }
            
            $image->status = 'processing';
            $image->progress = 0;
            $image->save();

            $inputPath = public_path($image->path); // Adjust if your path field is different
            $outputPath = public_path($this->outputPath);

            $cmd = escapeshellcmd("python3 " . base_path('python/upscale.py') . " " . escapeshellarg($inputPath) . " " . escapeshellarg($outputPath) . " " . intval($this->scale));
            
            // Execute command and capture output in real-time
            $descriptorspec = array(
                0 => array("pipe", "r"),  // stdin
                1 => array("pipe", "w"),  // stdout
                2 => array("pipe", "w")   // stderr
            );
            
            $process = proc_open($cmd, $descriptorspec, $pipes);
            
            if (is_resource($process)) {
                // Close stdin
                fclose($pipes[0]);
                
                // Read stdout and stderr in real-time
                $stdout = $pipes[1];
                $stderr = $pipes[2];
                
                // Set non-blocking mode
                stream_set_blocking($stdout, false);
                stream_set_blocking($stderr, false);
                
                $startTime = time();
                $timeout = 300; // 5 minutes timeout
                
                while (true) {
                    $status = proc_get_status($process);
                    if (!$status['running']) {
                        break;
                    }
                    
                    // Check for timeout
                    if (time() - $startTime > $timeout) {
                        proc_terminate($process);
                        throw new \Exception("Process timed out after {$timeout} seconds");
                    }
                    
                    // Read stdout
                    $line = fgets($stdout);
                    if ($line !== false) {
                        $line = trim($line);
                        
                        // Check for progress updates
                        if (preg_match('/^PROGRESS:(\d+):(.+)$/', $line, $matches)) {
                            $progress = (int)$matches[1];
                            $message = $matches[2];
                            
                            // Update progress in database
                            $image->progress = $progress;
                            $image->progress_message = $message;
                            $image->save();
                            
                            Log::info("Upscale progress: {$progress}% - {$message}", [
                                'image_id' => $this->imageId,
                                'progress' => $progress,
                                'message' => $message
                            ]);
                        } else {
                            // Log other output
                            Log::info("Python output: {$line}", ['image_id' => $this->imageId]);
                        }
                    }
                    
                    // Read stderr
                    $errorLine = fgets($stderr);
                    if ($errorLine !== false) {
                        Log::error("Python stderr: " . trim($errorLine), ['image_id' => $this->imageId]);
                    }
                    
                    // Small delay to prevent CPU spinning
                    usleep(100000); // 0.1 seconds
                }
                
                // Close pipes
                fclose($stdout);
                fclose($stderr);
                
                // Get return code
                $returnVar = proc_close($process);
                
                if ($returnVar === 0 && file_exists($outputPath)) {
                    $image->status = 'done';
                    $image->progress = 100;
                    $image->result_path = $this->outputPath;
                } else {
                    $image->status = 'failed';
                    $image->error_message = "Process failed with return code: {$returnVar}";
                }
                $image->save();
            } else {
                throw new \Exception("Failed to start Python process");
            }
            
        } catch (Exception $e) {
            Log::error('UpscaleImageJob failed: ' . $e->getMessage(), [
                'image_id' => $this->imageId,
                'scale' => $this->scale,
                'output_path' => $this->outputPath,
                'exception' => $e
            ]);
            
            // Update image status to failed
            if (isset($image)) {
                $image->status = 'failed';
                $image->error_message = 'Job failed: ' . $e->getMessage();
                $image->save();
            }
            
            // Re-throw the exception so Laravel can handle it properly
            throw $e;
        }
    }
}
