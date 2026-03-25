<?php

namespace App\Http\Controllers\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator};
use Carbon\Carbon;

use App\Models\{Grn, IssuedType,Inventories,ReturnStock};
use App\Helpers\{NumberFormatterHelper, TruncateWithTooltipHelper,StockQuantityHelper};
use App\Http\Controllers\Buyer\InventoryController;

use App\Exports\StockReturnReportExport;
use App\Services\ExportService;
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class StockReturnController extends Controller
{
    use TrimFields;
    public function __construct(protected ExportService $exportService) {}
    public function fetchInventoryDetails(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|integer|exists:inventories,id',
        ]);

        $inventory = Inventories::with(['product:id,product_name', 'uom:id,uom_name'])
            ->findOrFail($request->inventory_id);

        $companyId = Auth::user()->parent_id ?: Auth::user()->id;

        $currentStockValue = Cache::remember(
            "inventory_stock_{$companyId}_{$inventory->id}",
            now()->addMinutes(5),
            fn () => StockQuantityHelper::getCurrentStock(
                $inventory->id,
                $inventory->opening_stock,
                $companyId
            )
        );

        if ($currentStockValue <= 0) {
            return response()->json([
                'status' => 0,
                'message' => 'No stock available to issue.'
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => [
                'product_name' => $inventory->product->product_name ?? '',
                'specification' => cleanInvisibleCharacters($inventory->specification ?? ''),
                'size' => cleanInvisibleCharacters($inventory->size ?? ''),
                'uom_name' => $inventory->uom->uom_name ?? '',
                'uom_id' => $inventory->uom->id ?? '',
                'StockReturnType' => IssuedType::select('id', 'name')->get(),
                'stockReturnfromList' => IssuedController::getIssueFromList($inventory),
            ]
        ]);
    }


    public function store(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $attributeNames = [
            'qty' => 'Quantity',
            'inventory_id' => 'Inventory',
            'stock_return_for' => 'Stock Return Source',
            'stock_return_type' => 'Stock Return Type',
        ];
        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|exists:inventories,id',
            'qty' => 'required|numeric|min:0.001',
            'stock_return_type' => 'required|exists:issued_types,id',
            'remarks' => 'nullable|string|max:255',
            'stock_return_for' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !Grn::where('id', $value)->exists()) {
                        $fail('The selected stock return source is invalid.');
                    }
                },
            ],
        ]);
        $validator->setAttributeNames($attributeNames);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed. Please correct the errors below.',
                'errors' => $validator->errors()
            ], 422);
        }

        $inventory = Inventories::findOrFail($request->inventory_id);
        $issueFromList = IssuedController::getIssueFromList($inventory);

        $matchedSource = collect($issueFromList)->first(function ($source) use ($request) {
            return (int) $source['id'] === (int) $request->stock_return_for;
        });

        if (!$matchedSource) {
            return response()->json([
                'status' => 0,
                'message' => 'The selected stock return source is invalid or has no available stock.',
            ], 400);
        }

        if ($request->qty > $matchedSource['stockQty']) {
            return response()->json([
                'status' => 0,
                'message' => 'Requested quantity exceeds the available stock from the selected source.',
            ], 400);
        }

        $ReturnStock = new ReturnStock();
        $ReturnStock->inventory_id = $request->inventory_id;
        $ReturnStock->branch_id = $inventory->buyer_branch_id;
        $ReturnStock->buyer_id  = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;

        $lastStockReturn = ReturnStock::where('buyer_id', $ReturnStock->buyer_id)
                        ->orderBy('stock_no', 'desc')
                        ->first();

        $nextStockNo = $lastStockReturn ? $lastStockReturn->stock_no + 1 : 1;
        $ReturnStock->stock_no = $nextStockNo;
        $ReturnStock->qty = $request->qty;
        $ReturnStock->stock_return_for = $request->stock_return_for;

        $grn = Grn::with('manualOrder')->where('id', $request->stock_return_for)->first();
        $ReturnStock->vendor_name = $grn->vendor_name ?? '';
        $ReturnStock->remarks = cleanInvisibleCharacters($request->remarks ?? '');
        $ReturnStock->stock_vendor_name = $request->stock_vendor_name ?? '';
        $ReturnStock->updated_by = Auth::user()->id;
        $ReturnStock->updated_at = now();
        $ReturnStock->stock_vehicle_no_lr_no = $request->stock_vehicle_no_lr_no ?? '';
        $ReturnStock->stock_debit_note_no = $request->stock_debit_note_no ?? '';
        $ReturnStock->stock_frieght = $request->stock_frieght ?? '';
        $ReturnStock->stock_return_type = $request->stock_return_type ?? '';
        $ReturnStock->save();

        return response()->json([
            'status' => 1,
            'message' => 'Stock return has been successfully created.',
            'data' => $ReturnStock
        ], 201);
    }
    //start report
    public function applyFilters(Request $request)
    {
        $query = ReturnStock::select([
                'id', 'stock_no', 'inventory_id', 'stock_return_type',
                'qty', 'remarks', 'updated_at', 'updated_by', 'buyer_id'
            ])->with([
            'inventory:id,product_id,uom_id,specification,size,inventory_grouping,buyer_branch_id,buyer_product_name',
            'inventory.product:id,product_name',
            'inventory.uom:id,uom_name',
            'updater:id,name',
            'inventory.branch',
            'returnType'
        ])->orderBy('id', 'desc');

        if (session('branch_id') != $request->branch_id) {
                session(['branch_id' => $request->branch_id]);
            }
        $query->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->whereHas('inventory.branch', fn($q2) =>
                $q2->where('branch_id', $request->branch_id)
            );
        });

        // $query->when($request->filled('search_product_name'), function ($q) use ($request) {
        //     $q->whereHas('inventory.product', fn($q2) =>
        //         $q2->where('product_name', 'like', '%' . $request->search_product_name . '%')
        //     );
        // });
        $query->when($request->search_product_name, function ($q, $searchProductName) {
            $q->where(function ($subQuery) use ($searchProductName) {
                $subQuery->whereHas('inventory', function ($q1) use ($searchProductName) {
                    $q1->where('buyer_product_name', 'like', "%$searchProductName%");
                })
                ->orWhereHas('inventory.product', function ($q2) use ($searchProductName) {
                    $q2->where('product_name', 'like', "%$searchProductName%");
                });
            });
        });

        $query->when($request->filled('search_buyer_id'), function ($q) use ($request) {
            $q->where('updated_by', $request->search_buyer_id);
        });

        $query->when($request->filled('search_return_type'), function ($q) use ($request) {
            $q->where('stock_return_type', $request->search_return_type);
        });

        $query->when(!empty($request->search_category_id), function ($q) use ($request) {
            $catIds = InventoryController::getIdsByCategoryName($request->search_category_id);
            if (!empty($catIds)) {
                $q->whereHas('inventory.product', fn($q2) =>
                    $q2->whereIn('category_id', $catIds)
                );
            }
        });
        $query->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
            $fromDate = Carbon::createFromFormat('d/m/Y', trim($request->input('from_date')))->startOfDay();
            $toDate = Carbon::createFromFormat('d/m/Y', trim($request->input('to_date')))->endOfDay();
            $q->whereBetween('updated_at', [$fromDate, $toDate]);
        });

        $query->where('buyer_id',Auth::user()->parent_id ?? Auth::user()->id);
        return $query;
    }
    public function stockReturnReportlistdata(Request $request)
    {
        if (!$request->ajax()) return;
        $query = $this->applyFilters($request);
        $perPage = intval($request->length ?? 25);
        $perPage = ($perPage > 100) ? 100 : $perPage;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();
        $data = $this->formatIssuedData($inventories);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
    private function formatIssuedData($rows)
    {
        $data = [];
        $currencySymbol = session('user_currency')['symbol'] ?? '₹';
        foreach ($rows as $row) {
           $data[] = [
                    'stock_number'        => $row->stock_no,
                    'product'             => $row->inventory->product->product_name ?? TruncateWithTooltipHelper::wrapTextSS($row->inventory->buyer_product_name),
                    'buyer_product_name'  => TruncateWithTooltipHelper::wrapTextSS($row->inventory->buyer_product_name) ?? '',
                    'specification'       => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($row->inventory->specification ?? '')),
                    'size'                => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($row->inventory->size ?? '')),
                    'inventory_grouping'  => TruncateWithTooltipHelper::wrapText($row->inventory->inventory_grouping ?? ''),
                    'stock_return_type'   => $row->returnType->name ?? '',
                    'added_bY'            => $row->updater->name ?? '',
                    'added_date'          => $row->updated_at ? Carbon::parse($row->updated_at)->format('d/m/Y') : '',
                    'quantity'            => ($row->returnType->id == '1')
                                            ? NumberFormatterHelper::formatQty($row->qty, $currencySymbol)
                                            : '<span class="stock-return-details" style="cursor:pointer;color:blue;" data-id="' . $row->id . '">' .
                                                NumberFormatterHelper::formatQty($row->qty, $currencySymbol) .
                                            '</span>',
                    'uom'                 => $row->inventory->uom->uom_name ?? '',
                    'remarks'             => TruncateWithTooltipHelper::wrapText($row->remarks ?? ''),
                ];

        }
        return $data;
    }
    public function exportStockReturnReport(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(3000);
        $currencySymbol = session('user_currency')['symbol'] ?? '₹';
        $filters = $request->only([
                'branch_id',
                'search_product_name',
                'search_category_id',
                'search_return_type',
                'search_buyer_id',
                'from_date',
                'to_date',
            ]);

        $export = new StockReturnReportExport($filters, $currencySymbol);
        $fileName = 'Stock_Return_Report_' . now()->format('d-m-Y') . '.xlsx';

        $response = $this->exportService->storeAndDownload($export, $fileName);

        return response()->json($response);
    }
    public function exportTotal(Request $request)
    {
        $query = $this->applyFilters($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    public function exportBatch(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));

        $query = $this->applyFilters($request);

        $results = $query->offset($offset)->limit($limit)->get();
        $inventoryIds =  (clone $results)->pluck('id')->toArray();
        $quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        $currency = session('user_currency')['symbol'] ?? '₹';
        $result = [];
        foreach ($results as $index => $row) {
            $result[] = [
                    $row->stock_no,
                    optional($row->inventory->branch)->name ?? '',
                    $row->inventory->product->product_name ?? $row->inventory->buyer_product_name,
                    $row->inventory->buyer_product_name ?? '',
                    cleanInvisibleCharacters($row->inventory->specification ?? ''),
                    cleanInvisibleCharacters($row->inventory->size ?? ''),
                    $row->inventory->inventory_grouping ?? '',
                    $row->returnType->name ?? '',
                    $row->updater->name ?? '',
                    $row->updated_at ? Carbon::parse($row->updated_at)->format('d/m/Y') : '',
                    " " . NumberFormatterHelper::formatQty($row->qty, $currency),
                    $row->inventory->uom->uom_name ?? '',
                    cleanInvisibleCharacters($row->remarks ?? ''),
                ];

        }
        return response()->json(['data' => $result]);
    }

    public function fetchStockReturnRowdata(Request $request)
    {
        $ReturnStock = ReturnStock::with(['inventory', 'inventory.product', 'inventory.uom'])->find($request->id);

        if (!$ReturnStock) {
            return response()->json([
                'html' => '<p class="text-danger">Stock Return not found.</p>',
            ]);
        }

        return response()->json([
            'id' => $ReturnStock->id,
            'product_name' => $ReturnStock->inventory->product->product_name ?? $ReturnStock->inventory->buyer_product_name,
            'specification' =>  TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($ReturnStock->inventory->specification) ?? '-'),
            'size' =>  TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($ReturnStock->inventory->size) ?? '-'),
            'uom' => $ReturnStock->inventory->uom->uom_name ?? '-',
            'qty' => NumberFormatterHelper::formatQty(
                $ReturnStock->qty,
                session('user_currency')['symbol'] ?? '₹'
            ),
            'stock_vendor_name' => $ReturnStock->stock_vendor_name ?? '',
            'stock_vehicle_no_lr_no' => $ReturnStock->stock_vehicle_no_lr_no ?? '',
            'stock_debit_note_no' => $ReturnStock->stock_debit_note_no ?? '',
            'stock_frieght' => $ReturnStock->stock_frieght ?? '',
            'remarks' => $ReturnStock->remarks ? cleanInvisibleCharacters($ReturnStock->remarks) : '',
        ]);
    }

    public function editStockReturnRowdata(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:return_stocks,id',
            'remarks'                 => ['nullable', 'string', 'max:250', new NoSpecialCharacters(true)],
            'stock_vendor_name'       => ['nullable', 'string', 'max:255', new NoSpecialCharacters(false)],
            'stock_vehicle_no_lr_no'  => ['nullable', 'string', 'max:50', new NoSpecialCharacters(false)],
            'stock_debit_note_no'     => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'stock_frieght'           => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
        ]);

        try {
            $ReturnStock = ReturnStock::findOrFail($request->id);

            $ReturnStock->remarks = $request->remarks;
            $ReturnStock->stock_vendor_name = $request->stock_vendor_name;
            $ReturnStock->stock_vehicle_no_lr_no = $request->stock_vehicle_no_lr_no;
            $ReturnStock->stock_debit_note_no = $request->stock_debit_note_no;
            $ReturnStock->stock_frieght = $request->stock_frieght;
            $ReturnStock->save();

            return response()->json(['status' => 'success', 'message' => 'Stock Return updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update GRN.']);
        }
    }
    //end report


}
