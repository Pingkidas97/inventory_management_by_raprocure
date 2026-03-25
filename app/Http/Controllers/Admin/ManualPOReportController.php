<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\{User, ManualOrder};
use App\Http\Controllers\Buyer\InventoryController;

use App\Exports\SAManualPoReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;



class ManualPOReportController extends Controller
{
    public function index()
    {
       return view('admin.reports.manualpo');
    }
    public static function userCurrency($userId): void
    {
        $user = User::with('currencyDetails')->find($userId);
        if ($user && $user->currencyDetails) {
            session([
                'user_currency' => [
                    'id' => $user->currencyDetails->id,
                    'symbol' => $user->currencyDetails->currency_symbol,
                ]
            ]);
        }
    } 

    public function export(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(3000);
        $filters = $request->only([
                'branch_id',
                'search_product_name',
                'search_category_id',
                'search_vendor_name',
                'search_order_no',
                'order_status',
                'from_date',
                'to_date',
            ]);

        $export = new SAManualPoReportExport($filters);
        $fileName = 'Manual_PO_Report.xlsx';

        $response = $this->storeAndDownload($export, $fileName);

        return response()->json($response);
    }

    public function getFilteredQuery(Request $request)
    {
        if (session('branch_id') != $request->branch_id) {
                session(['branch_id' => $request->branch_id]);
            }

        $query = ManualOrder::with(['vendor','buyer', 'preparedBy', 'products.product','products.inventory.branch']);

        $query->when($request->filled(['from_date', 'to_date']), function ($q) use ($request) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
            $to   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();

            $q->whereBetween('created_at', [$from, $to]);
        })           
            
            ->orderBy('id', 'desc')->orderBy('created_at', 'desc');

        return $query;
    }   

    public static function formatCurrency($amount,$currency): string
    {
        if($amount==0 || $amount=='0'){
            return $currency .' '.$amount;
        }
        return match ($currency) {
            '₹' => '₹ ' .self::formatINR($amount),
            'रु' => 'रु ' .self::formatNPR($amount),
            '$' => '$ ' .self::formatUSD($amount),
            default => $currency . ' ' . sprintf("%.2f", (float)$amount),
        };
    }    

    public static function formatINR($amount): string
    {
        $amount = (float)$amount;
        $amountFormatted = number_format($amount, 2, '.', '');

        [$intPart, $decimalPart] = explode('.', $amountFormatted);

        $lastThree = substr($intPart, -3);
        $restUnits = substr($intPart, 0, -3);

        if ($restUnits != '') {
            $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
        }

        $formatted = ($restUnits != '') ? $restUnits . ',' . $lastThree : $lastThree;

        return $formatted . '.' . $decimalPart;
    }

    public static function formatNPR($amount): string
    {
        return self::formatINR($amount);
    }

    public static function formatUSD($amount): string
    {
        return  number_format((float) $amount, 2, '.', ',');
    }

    public function storeAndDownload($export, string $fileName): array
    {
        try {
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
            } elseif (method_exists($export, 'collection')) {
                $collection = $export->collection();
                if ($collection->isEmpty()) {
                    return [
                        'success' => false,
                        'fetchRow' => false,
                        'message' => 'No record found for export, Try another search!.'
                    ];
                }
            }

            // Unique folder path
            $userId = auth()->id().'_'.time().'_'.rand(1000, 9999);
            $storageFolder = "exports/{$userId}";
            $filePath = "{$storageFolder}/{$fileName}";
            $storageDir = storage_path("app/public/{$storageFolder}");

            // Ensure storage directory exists
            if (!File::exists($storageDir)) {
                File::makeDirectory($storageDir, 0775, true);
            }

            // Attempt to store Excel file
            $stored = Excel::store($export, $filePath, 'public');

            // Store failed
            if (!$stored) {
                return [
                    'success' => false,
                    'message' => 'Excel::store() returned false.',
                    'path' => $filePath,
                    'storage_dir' => $storageDir
                ];
            }

            // Move to public uploads for download
            $sourcePath = storage_path('app/public/' . $filePath);
            $destinationDir = public_path("uploads/exl/{$userId}");
            $destinationPath = $destinationDir . '/' . $fileName;

            if (!File::exists($destinationDir)) {
                File::makeDirectory($destinationDir, 0775, true);
            }

            File::move($sourcePath, $destinationPath);

            // Optional cleanup
            if (is_dir($storageDir) && count(scandir($storageDir)) <= 2) {
                rmdir($storageDir);
            }

            return [
                'success' => true,
                'download_url' => route('admin.reports.manualpoReport.downloadAndDelete', [
                    'path' => Crypt::encrypt("uploads/exl/{$userId}/{$fileName}")
                ])
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error during export: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
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
