<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedImage extends Model
{
    protected $fillable = [
        'path',
        'status',
        'result_path',
        'error_message',
    ];
}
