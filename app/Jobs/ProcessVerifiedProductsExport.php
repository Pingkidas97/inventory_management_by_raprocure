<?php

namespace App\Jobs;

use App\Models\ExportJob;
use App\Exports\VerifiedProductsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ProcessVerifiedProductsExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 1800;

    public function __construct(public ExportJob $exportJob)
    {
    }

    public function handle()
    {
        try {
            $this->exportJob->update(['status' => 'processing']);
        
            $fileName = $this->exportJob->export_id . '.xlsx';
            $filePath = 'exports/' . $fileName;

            // Ensure directory exists - PUBLIC storage
            Storage::disk('public')->makeDirectory('exports');
            
            // Save to PUBLIC storage
            Excel::store(
                new VerifiedProductsExport(),
                $filePath,
                'public',  // Explicitly using public disk
                ExcelFormat::XLSX
            );

            // Count records (optional)
            $count = DB::table('vendor_products')
                ->where('edit_status', 0)->where('approval_status', 1)
                ->count();

            // Update job with public URL
            $this->exportJob->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'public_url' => Storage::disk('public')->url($filePath),
                'record_count' => $count,
                'completed_at' => now(),
            ]);

        } catch (\Exception $e) {
            $this->exportJob->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        $this->exportJob->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}