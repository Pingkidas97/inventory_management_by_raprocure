<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportJob extends Model
{
    protected $fillable = [
        'export_id',  
        'file_name',
        'type',
        'language',
        'record_count',
        'status',
        'error_message',
        'disk',
        'file_path'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];
}