<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\File;
class NotifyExportFinished implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $exportId;

    public function __construct($exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle()
    {
        $from = storage_path("app/private/exports/{$this->exportId}.xlsx");
        $to = public_path("exports/{$this->exportId}.xlsx");
        if(file_exists($from)){
            File::move(
                $from ,
                $to
            );
        }
        cache()->put("export_progress:{$this->exportId}", 100, now()->addMinutes(30));
    }
}

