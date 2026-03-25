<?php

namespace App\Http\Controllers\Buyer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator};
use Carbon\Carbon;

use App\Models\{Issued, Grn, IssuedReturn, IssuedType, Inventories};
use App\Helpers\{NumberFormatterHelper, TruncateWithTooltipHelper};
use App\Http\Controllers\Buyer\InventoryController;

use App\Exports\IssueReturnReportExport;
use App\Services\ExportService;
use App\Traits\TrimFields;

use App\Http\Controllers\Controller;

class IssueReturnController extends Controller
{
    use TrimFields;
    public function __construct(protected ExportService $exportService) {}

//start add issue return
    public function fetchInventoryDetails(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|integer|exists:inventories,id',
        ]);

        $inventory = Inventories::with(['product', 'uom'])->find($request->inventory_id);
        if ((int) $inventory->product_id === 0) {
                $inventory->setRelation('product', (object) [
                    'id'            => 0,
                    'product_name'  => $inventory->buyer_product_name,
                    'category_id'   => null,
                    'division_id'   => null,
                ]);
            }
        if (!$inventory) {
            return response()->json([
                'status' => 0,
                'message' => 'Inventory not found.'
            ], 404);
        }

        $getIssueFromList = $this->getIssueFromList($inventory);

        if ($getIssueFromList->count() == 0) {
            return response()->json([
                'status' => 0,
                'message' => 'No Issue Stock available to Issue Return.'
            ]);
        }


        return response()->json([
            'status' => 1,
            'data' => [
                'product_name'    => $inventory->product->product_name ?? '',
                'specification'   => cleanInvisibleCharacters($inventory->specification ?? ''),
                'size'            => cleanInvisibleCharacters($inventory->size ?? ''),
                'uom_name'        => $inventory->uom->uom_name ?? '',
                'uom_id'          => $inventory->uom->id ?? '',
                'IssuedType'    => IssuedType::all(['id', 'name']),
                'issuefromList' => $getIssueFromList
            ]
        ]);
    }
    private function getIssueFromList($inventory)
    {
        $issues = Issued::selectRaw('issued_return_for, SUM(qty) as total_qty, SUM(consume_qty) as total_consume_qty')
            ->where('inventory_id', $inventory->id)
            ->groupBy('issued_return_for')
            ->get();

        return $issues->map(function($issue) use ($inventory) {
            $returnQty = $this->fetchReturnQty($inventory->id, $issue->issued_return_for);
            $remainingQty = round($issue->total_qty - $issue->total_consume_qty - $returnQty, 3);

            if ($remainingQty > 0) {
                $id = $issue->issued_return_for;
                $label = ($id == 0) ? 'Opening Stock' : $this->fetchPONumberByGrnID($id);
                return [
                    'label' => $label,
                    'id' => $id,
                    'stock' => NumberFormatterHelper::formatQty($remainingQty,session('user_currency')['symbol'] ?? '₹'),
                    'stockQty' => $remainingQty,
                ];
            }
        })->filter()->values();
    }


    private function fetchPONumberByGrnID($grnId)
    {
        $grn = Grn::with('manualOrder')->find($grnId);
        return trim($grn->po_number . ' / ' . $grn->vendor_name) ?: 'N/A';
    }
    private function fetchReturnQty($inventory, $issued_return_for)
    {
        $qry_return_issued = IssuedReturn::selectRaw('SUM(qty) as total_issued_return')
            ->where('inventory_id', $inventory)
            ->where('issued_return_for', $issued_return_for)
            ->where('is_deleted', 2)
            ->groupBy('issued_return_for', 'inventory_id')
            ->first();

        return $qry_return_issued->total_issued_return ?? 0;
    }
    public function store(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);

        $attributeNames = [
            'qty' => 'quantity',
            'inventory_id' => 'inventory',
            'issued_return_for' => 'issue From',
        ];

        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|exists:inventories,id',
            'qty' => 'required|numeric|min:0.001',
            'issued_return_type' => 'nullable|exists:issued_types,id',
            'remarks' => 'nullable|string|max:255',
            'issued_return_for' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !Grn::where('id', $value)->exists()) {
                        $fail('The selected issue source is invalid.');
                    }
                },
            ],
        ]);

        $validator->setAttributeNames($attributeNames);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $inventory = Inventories::findOrFail($request->inventory_id);

        $availableSources = $this->getIssueFromList($inventory);

        $matchedSource = collect($availableSources)->first(function ($source) use ($request) {
            return (int) $source['id'] === (int) $request->issued_return_for;
        });

        if (!$matchedSource) {
            return response()->json([
                'status' => 0,
                'message' => 'The selected source is invalid or has no available stock.',
            ], 400);
        }

        if ($request->qty > $matchedSource['stockQty']) {
            return response()->json([
                'status' => 0,
                'message' => 'Requested quantity exceeds available stock from the selected source.',
            ], 400);
        }

        $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

        $lastReturn = IssuedReturn::where('buyer_id', $buyerId)
            ->orderBy('issue_unique_no', 'desc')
            ->first();

        $nextIssueNo = $lastReturn ? $lastReturn->issue_unique_no + 1 : 1;

        $issuedReturn = new IssuedReturn();
        $issuedReturn->inventory_id = $request->inventory_id;
        $issuedReturn->branch_id = $inventory->buyer_branch_id;
        $issuedReturn->buyer_id = $buyerId;
        $issuedReturn->issue_unique_no = $nextIssueNo;
        $issuedReturn->qty = $request->qty;
        $issuedReturn->issued_return_for = $request->issued_return_for;

        if ($request->issued_return_type) {
            $issuedReturn->issued_return_type = $request->issued_return_type;
        }

        if ($request->remarks) {
            $issuedReturn->remarks = cleanInvisibleCharacters($request->remarks ?? '');
        }

        $issuedReturn->updated_by = Auth::id();
        $issuedReturn->updated_at = now();
        $issuedReturn->save();

        return response()->json([
            'status' => 1,
            'message' => 'Issue Return created successfully.',
            'data' => $issuedReturn,
        ], 201);
    }


//end add issue return

//start issue Return report
    public function ApplyFilterQuery(Request $request)
    {
        $query = IssuedReturn::with([
            'inventory:id,product_id,uom_id,specification,size,inventory_grouping,buyer_branch_id,buyer_product_name',
            'inventory.product:id,product_name,category_id',
            'inventory.uom:id,uom_name',
            'updater:id,name',
            'inventory.branch:id,branch_id,name',
            'issuedType',
        ])->orderByDesc('id');

        $requestBranchId = $request->branch_id;
        if (session('branch_id') !== $requestBranchId) {
            session(['branch_id' => $requestBranchId]);
        }

        $query->when($request->filled('branch_id'), function ($q) use ($requestBranchId) {
            $q->whereHas('inventory.branch', fn($q2) =>
                $q2->where('branch_id', $requestBranchId)
            );
        });

        // $query->when($request->filled('search_product_name'), function ($q) use ($request) {
        //     $q->whereHas('inventory.product', fn($q2) =>
        //         $q2->where('product_name', 'like', '%' . $request->search_product_name . '%')
        //     );
        // });

        $query->when($request->filled('search_product_name'), function ($q) use ($request) {
            $q->where(function ($subQuery) use ($request) {
                $subQuery->whereHas('inventory', function ($q1) use ($request) {
                    $q1->where('buyer_product_name', 'like', "%$request->search_product_name%");
                })
                ->orWhereHas('inventory.product', function ($q2) use ($request) {
                    $q2->where('product_name', 'like', "%$request->search_product_name%");
                });
            });
        });


        $query->when($request->filled('search_buyer_id'), function ($q) use ($request) {
            $q->where('updated_by', $request->search_buyer_id);
        });

        $query->when(!empty($request->search_category_id), function ($q) use ($request) {
            $cat_id = InventoryController::getIdsByCategoryName($request->search_category_id);
            if (!empty($cat_id)) {
                $q->whereHas('inventory.product', fn($q2) =>
                    $q2->whereIn('category_id', $cat_id)
                );
            }
        });

        $query->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
            $fromDate = Carbon::createFromFormat('d/m/Y', trim($request->input('from_date')))->startOfDay();
            $toDate = Carbon::createFromFormat('d/m/Y', trim($request->input('to_date')))->endOfDay();
            $q->where(function ($query) use ($fromDate, $toDate) {
                $query->WhereBetween('updated_at', [$fromDate, $toDate]);
            });
        });
        $query->where('buyer_id', Auth::user()->parent_id ?? Auth::id());
        return $query;
    }
    public function getIssueReturnListData(Request $request)
    {
        if (!$request->ajax()) return;

        $query = $this->ApplyFilterQuery($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();
        $data = $this->formatIssueReturnRow($inventories);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
    private function formatIssueReturnRow($rows)
    {
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'issued_return_number' => $row->issue_unique_no,
                'product' => $row->inventory->product->product_name ?? TruncateWithTooltipHelper::wrapTextSS($row->inventory->buyer_product_name),
                'buyer_product_name' => TruncateWithTooltipHelper::wrapTextSS($row->inventory->buyer_product_name) ?? '',
                'specification' => TruncateWithTooltipHelper::wrapText($row->inventory->specification ?? ''),
                'size' =>TruncateWithTooltipHelper::wrapText($row->inventory->size ?? ''),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($row->inventory->inventory_grouping ?? ''),
                'issued_return_type' => $row->issuedType->name ?? '',
                'added_bY' => $row->updater->name ?? '',
                'added_date' => $row->updated_at ? Carbon::parse($row->updated_at)->format('d/m/Y') : '',
                'quantity' => NumberFormatterHelper::formatQty($row->qty,session('user_currency')['symbol'] ?? '₹'),
                'uom' => $row->inventory->uom->uom_name ?? '',
                'remarks' => TruncateWithTooltipHelper::wrapText($row->remarks ?? '') ,
            ];
        }
        return $data;
    }
    public function export(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(3000);
        $currencySymbol = session('user_currency')['symbol'] ?? '₹';
        $filters = $request->only([
                'branch_id',
                'search_product_name',
                'search_category_id',
                'search_buyer_id',
                'from_date',
                'to_date',
            ]);

        $export = new IssueReturnReportExport($filters, $currencySymbol);
        $fileName = 'Issue_Return_Report_' . now()->format('d-m-Y') . '.xlsx';

        $response = $this->exportService->storeAndDownload($export, $fileName);

        return response()->json($response);
    }
    public function exportTotal(Request $request)
    {
        $query = $this->ApplyFilterQuery($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    public function exportBatch(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));

        $query = $this->ApplyFilterQuery($request);

        $results = $query->offset($offset)->limit($limit)->get();
        $currency = session('user_currency')['symbol'] ?? '₹';
        $result = [];
        foreach ($results as $index => $row) {
            $result[] = [
                    $row->issue_unique_no,
                    optional($row->inventory->branch)->name ?? '',
                    $row->inventory->product->product_name ?? $row->inventory->buyer_product_name,
                    $row->inventory->buyer_product_name ?? '',
                    cleanInvisibleCharacters($row->inventory->specification ?? ''),
                    cleanInvisibleCharacters($row->inventory->size ?? ''),
                    $row->inventory->inventory_grouping ?? '',
                    $row->issuedType->name ?? '',
                    $row->updater->name ?? '',
                    $row->updated_at ? Carbon::parse($row->updated_at)->format('d/m/Y') : '',
                    " ".NumberFormatterHelper::formatQty($row->qty, $currency),
                    $row->inventory->uom->uom_name ?? '',
                    cleanInvisibleCharacters($row->remarks ?? ''),
                ];

        }
        return response()->json(['data' => $result]);
    }
//end issue Return Report
}
