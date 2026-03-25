<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVerifiedProductsExport;
use App\Models\ExportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportVerifiedProductController extends Controller
{
    public function index()
    {
        $exports = ExportJob::orderBy('created_at', 'desc')->paginate(1000);
        return view('admin.verified-products.export', compact('exports'));
    }

    public function exportVerifiedProducts(Request $request)
    {
        $exportId = Str::uuid();
        $fileName = 'verified_products_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        $exportJob = ExportJob::create([
            'export_id' => $exportId,
            'type' => 'verified_products',
            'file_name' => $fileName,
            'language' => 'English',
            'status' => 'processing',
            'disk' => 'public',
            'file_path' => 'exports/' . $fileName,
        ]);

        ProcessVerifiedProductsExport::dispatch($exportJob);

        return response()->json([
            'success' => true,
            'message' => 'Export has been started',
            'export_id' => $exportId,
        ]);
    }

    public function checkStatus($exportId)
    {
        $exportJob = ExportJob::where('export_id', $exportId)->firstOrFail();

        return response()->json([
            'status' => $exportJob->status,
            'completed' => $exportJob->status === 'completed',
            'failed' => $exportJob->status === 'failed',
            'download_url' => $exportJob->status === 'completed' 
                ? route('admin.exports.download', $exportJob->export_id) 
                : null,
            'error' => $exportJob->error_message,
        ]);
    }

    public function download($exportId)
    {
        // 1. Fetch completed export job
        $exportJob = ExportJob::where('export_id', $exportId)
            ->where('status', 'completed')
            ->firstOrFail();

        // 2. Get the storage disk (default: public)
        $disk = $exportJob->disk ?? 'public';

        // 3. Extract relative path from URL (remove domain part)
        $urlPath = parse_url($exportJob->file_path, PHP_URL_PATH); // e.g. /storage/exports/abc.xlsx

        // 4. Convert it to storage path (storage/app/public/exports/abc.xlsx)
        $relativePath = str_replace('storage/', '', ltrim($urlPath, '/')); // => exports/abc.xlsx

        // 5. Check if file exists
        if (!Storage::disk($disk)->exists($relativePath)) {
            abort(404, "Export file not found.");
        }

        // 6. Download with optional filename
        $filename = $exportJob->file_name ?? basename($relativePath);

        return Storage::disk($disk)->download($relativePath, $filename);
    }

}
