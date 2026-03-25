<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;


class ExportService
{
    public function storeAndDownload($export, string $fileName): array
    {
       
        $query = null;
        if (method_exists($export, 'query')) {
            $query = $export->query();
            if (!$query->exists()) {
                return [
                    'success' => false,
                    'fetchRow' => false,
                    'message' => 'No record found for export, Try another search!.'
                ];
            }
        }elseif (method_exists($export, 'collection')) {
            $collection = $export->collection();
            if ($collection->isEmpty()) {
                return [
                    'success' => false,
                    'fetchRow' => false,
                    'message' => 'No record found for export, Try another search!.'
                ];
            }
        }
        
        $userId = auth()->id().'_'.time().'_'.rand(1000, 9999);
        $storageFolder = "exports/{$userId}";
        $filePath = "{$storageFolder}/{$fileName}";
        $storageDir = storage_path("app/public/{$storageFolder}");
        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0775, true);
        }
        $stored = Excel::store($export, $filePath, 'public');
        

        if (!$stored) {
            return [
                'success' => false,
                'message' => 'Store failed'
            ];
        }
        $sourcePath = storage_path('app/public/' . $filePath);
        $destinationDir = public_path("uploads/exl/{$userId}");
        $destinationPath = $destinationDir . '/' . $fileName;
        
        if (!File::exists($destinationDir)) {
            File::makeDirectory($destinationDir, 0775, true); 
        }
        File::move($sourcePath, $destinationPath);
        
        if (is_dir($storageDir) && count(scandir($storageDir)) <= 2) {
            if (File::isDirectory($storageDir)) {
                chmod($storageDir, 0775); 
            }
            rmdir($storageDir);
        }
        
        return [
            'success' => true,
            'download_url' => route('buyer.downloadAndDelete', [
                                    'path' => Crypt::encrypt("uploads/exl/{$userId}/{$fileName}")
                                    ])
            ];
    }
    
    public function deleteExportFile(string $fileUrl): array
    {
        $relativePath = str_replace(asset('/'), '', $fileUrl); 
        $relativePath = ltrim(preg_replace('/^public\//', '', $relativePath), '/');
        $fullPath = public_path($relativePath);
        if (file_exists($fullPath)) {
            try {
                unlink($fullPath);
                $folderPath = dirname($fullPath);
                if (is_dir($folderPath) && count(scandir($folderPath)) <= 2) {
                    chmod($folderPath, 0775); 
                    rmdir($folderPath);
                }

                return ['success' => true];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'File deletion error: ' . $e->getMessage()
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'File not found at ' . $relativePath
        ];
    }
    public function downloadAndDeleteFile(string $encryptedPath)
    {
        $path = Crypt::decrypt($encryptedPath);
        $file = public_path($path);
        if (!file_exists($file)) {
            abort(404, 'File not found.');
        }
        $this->cleanupEmptyFolders('uploads/exl');
        return response()->download($file)->deleteFileAfterSend(true);
    }
    public function cleanupEmptyFolders(string $basePath)
    {
        $absolutePath = public_path($basePath);

        if (!is_dir($absolutePath)) {
            return 0;
        }

        $deletedCount = 0;

        $dirs = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absolutePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($dirs as $dir) {
            if ($dir->isDir()) {
                $files = scandir($dir->getRealPath());
                if (count($files) <= 2) {
                    rmdir($dir->getRealPath());
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }



}
