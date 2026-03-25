<?php
namespace App\Http\Controllers\Buyer;
use App\Exports\{
    CurrentStockExport,InventoryExport,StockLedgerExport,DeadStockExport,MinQtyExport
};
use App\Helpers\{
    CurrentStockReportAmountHelper,NumberFormatterHelper,StockQuantityHelper,TruncateWithTooltipHelper
};
use App\Http\Controllers\Controller;
use App\Models\{
    BranchDetail,Category,Inventories,InventoryType,Uom,Grn, Indent, Issued, ManualOrder,ReturnStock,Rfq,RfqProduct,RfqProductVariant,IndentRfq,OrderVariant,Product,IssuedReturn,Consume,Buyer
};

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,DB,Route,Validator,Cache
};
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;
use App\Traits\HasModulePermission;
use App\Http\Controllers\Buyer\ManualPOController;
use App\Http\Controllers\Buyer\GetPassController;
use App\Http\Controllers\Buyer\GrnController;

class InventoryController extends Controller
{
    use TrimFields;
    use HasModulePermission;
    protected $grnQtyCache = [];
    protected $rfqDataCache = [];
    protected $orderQtyCache = [];
    protected $batchSize = 100;

    public function index(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'view', '1');
            }
            ManualPOController::userCurrency();
            $branchId = $request->input('branch_id');
            $productId = $request->input('product_id');
            $categoryId = $request->input('category_id');
            $inventoryTypeId = $request->input('inventory_type_id');
            $user_id = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;

            $branches = BranchDetail::getDistinctActiveBranchesByUser($user_id);
            $categories = $this->getSortedUniqueCategoryNames();
            $inventoryTypes = InventoryType::all();
            $uom = Uom::all();

            $firstBranch = $branches->first();

            if (empty(session('branch_id')) && $firstBranch !== null) {
                session(['branch_id' => $firstBranch->branch_id]);
            }

            session(['page_title' => 'Inventory Management System - Raprocure']);

            return view('buyer.inventory.index', compact('branches', 'categories', 'inventoryTypes', 'uom'));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function store(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $attributeNames = [
            'buyer_product_name' => 'Our Product Name',
            'buyer_branch_id'    => 'Branch',
        ];
        if($request->type && $request->type == 'minQtyUpdate'){
            $requestData['indent_min_qty'] = trim($request->indent_min_qty);
            $requestData['updated_by'] = Auth::user()->id;
                $inventory = Inventories::updateOrCreate(
                    ['id' => $request->id],
                    $requestData
                );
            return response()->json([
                    'status' => true,
                    'message' => 'Min Qty updateed successfully!',
                    'data' => $inventory
                ], 200);
        }
        else{
            $validator = Validator::make($request->all(), [
                'inventory_unique_id' => 'nullable|integer',
                'buyer_parent_id'     => 'nullable|integer|min:0',
                'buyer_branch_id'     => 'required|exists:branch_details,branch_id',
                'product_id'          => 'nullable|integer|min:0',

                'product_name'        => ['nullable', 'string', 'max:100', new NoSpecialCharacters(false)],
                'buyer_product_name'  => ['nullable', 'string', 'max:100', new NoSpecialCharacters(false)],
                'specification'       => ['nullable', 'string', 'max:3000', new NoSpecialCharacters(true)],
                'size'                => ['nullable', 'string', 'max:1500', new NoSpecialCharacters(true)],
                'opening_stock'       => ['required', 'regex:/^\d+(\.\d+)?$/', 'max:20', new NoSpecialCharacters(false)],
                'inventory_grouping'  => ['nullable', 'string', 'max:255', new NoSpecialCharacters(false)],
                'cost_center'         => ['nullable', 'string', 'max:255', new NoSpecialCharacters(true)],
                'product_brand'       => ['nullable', 'string', 'max:255', new NoSpecialCharacters(true)],
                'stock_price'         => ['required', 'numeric', 'min:0', new NoSpecialCharacters(false)],
                'indent_min_qty'      => ['nullable', 'regex:/^\d+(\.\d+)?$/', 'max:20', new NoSpecialCharacters(false)],

                'uom_id'              => 'required|exists:uoms,id',
                'inventory_type_id'   => 'nullable|integer|min:1',
            ]);

            $validator->setAttributeNames($attributeNames);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            try {
                $requestData = $request->except(['_token']);
                $requestData['buyer_parent_id'] = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
                if ($request->id >= 1) {
                    $count = Inventories::
                            where(function ($query) use ($request) {
                                if (!empty($request->product_id) && $request->product_id != 0) {
                                    $query->where('product_id', $request->product_id);
                                } else {
                                    $query->whereRaw('LOWER(buyer_product_name) = ?', [strtolower(trim($request->buyer_product_name))]);
                                }

                            })
                        ->whereRaw('LOWER(specification) = ?', [strtolower(trim($request->specification))])
                        ->whereRaw('LOWER(size) = ?', [strtolower(trim($request->size))])
                        ->where('id', '<>', $request->id)
                        ->where('buyer_branch_id', $request->buyer_branch_id)
                        ->count();
                    $exists = Inventories::where('buyer_parent_id', $requestData['buyer_parent_id'])
                                ->where('buyer_branch_id', $request->buyer_branch_id)
                                ->where('item_code', $request->item_code)
                                ->where('id', '<>', $request->id)
                                ->exists();

                    if ($exists) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Item code already exists for this branch and buyer!',
                        ], 200);                        
                    }
                    
                } else {
                    $count = Inventories::where('product_id', $request->product_id)
                        ->whereRaw('LOWER(specification) = ?', [strtolower(trim($request->specification))])
                        ->whereRaw('LOWER(size) = ?', [strtolower(trim($request->size))])
                        ->where('buyer_branch_id', $request->buyer_branch_id)
                        ->count();
                    
                       
                    $requestData['item_code'] = $this->generateItemCode(
                            $requestData['buyer_parent_id'],
                            $request->buyer_branch_id
                        );
                }

                if ($count == 0) {
                    if($request->product_id == null){
                        $requestData['product_id'] = 0;
                    }
                    

                    if (empty($request->id)) {
                        $lastInventory = Inventories::where('buyer_parent_id', $requestData['buyer_parent_id'])
                            ->where('buyer_branch_id', $request->buyer_branch_id)
                            ->orderBy('inventory_unique_id', 'desc')
                            ->first();

                        $nextInventoryId = $lastInventory ? $lastInventory->inventory_unique_id + 1 : 1;
                        $requestData['inventory_unique_id'] = $nextInventoryId;
                        $requestData['created_by'] = Auth::user()->id;
                    }

                    $requestData['updated_by'] = Auth::user()->id;
                    $requestData['indent_min_qty'] = round($request->indent_min_qty,3);
                    $requestData['specification'] = trim($request->specification);
                    $requestData['size'] = trim($request->size);
                    $inventory = Inventories::updateOrCreate(
                        ['id' => $request->id],
                        $requestData
                    );

                    return response()->json([
                        'status' => true,
                        'message' => 'Inventory added successfully!',
                        'data' => $inventory
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Please Add Unique Size Or Specification With this Product!',
                    ], 200);
                }

            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Inventory not added, please try again!',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }
    function generateItemCode($buyerParentId, $buyerBranchId)
    {
        // Get branch short (first 2 letters)
        $branchName = BranchDetail::where('branch_id', $buyerBranchId)
            ->where('record_type', 1)
            ->where('user_type', 1)
            ->value('name');

        $branchShort = strtoupper(substr($branchName, 0, 2));

        // Get organization short (first 2 letters)
        $orgShortCode = Buyer::where('user_id', $buyerParentId)
            ->value('organisation_short_code');

        $orgShort = strtoupper(substr($orgShortCode, 0, 2));

        // Prefix
        $prefix = $orgShort . $branchShort;

        // Get last item
        $lastItem = Inventories::where('buyer_parent_id', $buyerParentId)
            ->where('buyer_branch_id', $buyerBranchId)
            ->where('item_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastItem) {
            $lastSeq = (int) substr($lastItem->item_code, -4);
            $seq = $lastSeq + 1;
        } else {
            $seq = 1;
        }

        // Ensure uniqueness
        do {
            $seqStr = str_pad($seq, 4, '0', STR_PAD_LEFT);
            $itemCode = $prefix . '-' . $seqStr;

            $exists = Inventories::where('buyer_parent_id', $buyerParentId)
                ->where('buyer_branch_id', $buyerBranchId)
                ->where('item_code', $itemCode)
                ->exists();

            if ($exists) {
                $seq++;
            }

        } while ($exists);

        return $itemCode;
    }
    public function edit($id)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'edit', '1');
            }
            $data = Inventories::with([
                'product:id,product_name,category_id,division_id',
                'product.category:id,category_name',
                'product.division:id,division_name'
            ])
            ->select(
                'id', 'product_id', 'buyer_product_name', 'specification', 'size',
                'opening_stock', 'stock_price', 'uom_id', 'inventory_grouping','cost_center',
                'inventory_type_id', 'indent_min_qty', 'product_brand','item_code'
            )
            ->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory not found!'
                ], 404);
            }
            $editData = 1;
            $nonEditEnv = 0;
            $productOnlyEdit = 0;                     
            $openingStockEditable = 0;
            $usedOpeningStock=0;
            if ($data->product_id == 0) {
                $productOnlyEdit = 1;
                if (Indent::where('inventory_id', $id)->exists()) {
                    $editData = 0;
                }
                if (Issued::where('inventory_id', $id)->exists()) {
                    $editData = 0;
                    $nonEditEnv = 1;
                }
                if (Grn::where('inventory_id', $id)->exists()) {
                    $editData = 0;
                }

                if (ReturnStock::where('inventory_id', $id)->exists()) {
                    $editData = 0;
                    $nonEditEnv = 1;
                }
            }else{
                if (Indent::where('inventory_id', $id)->exists()) {
                    $editData = 0;
                }

                if (Issued::where('inventory_id', $id)->exists()) {
                    $editData = 0;
                    $nonEditEnv = 1;
                }

                if (Grn::where('inventory_id', $id)->exists()) {
                    $editData = 0;
                }

                if (ReturnStock::where('inventory_id', $id)->exists()) {
                    $editData = 0;
                    $nonEditEnv = 1;
                }
            }

            if (Auth::user()->parent_id == 0) {
                $openingStockEditable = 1;
                $openingStockIssue= Issued::where('inventory_id', $id)->where('issued_return_for','0')->sum('qty');
                $openingStockMaxIssue= Issued::where('inventory_id', $id)->where('issued_return_for','0')->max('qty');
                $openingStockIssueReturn= IssuedReturn::where('inventory_id', $id)->where('issued_return_for','0')->sum('qty');
                $openingStockStockReturn= ReturnStock::where('inventory_id', $id)->where('stock_return_for','0')->sum('qty');
                $openingStockMaxStockReturn= ReturnStock::where('inventory_id', $id)->where('stock_return_for','0')->max('qty');
                $openingStockStockReturnGrn= Grn::where('inventory_id', $id)->where('grn_type','4')->where('order_id','0')->sum('grn_qty');
                $usedOpeningStock= 0 - $openingStockIssue + $openingStockIssueReturn - $openingStockStockReturn + $openingStockStockReturnGrn;
                if ($usedOpeningStock < 0) {
                    $usedOpeningStock = $usedOpeningStock * -1;
                }
                $usedOpeningStock = max($usedOpeningStock, $openingStockMaxIssue, $openingStockMaxStockReturn);

            }
            
            
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'edit_data' => $editData,
                'non_edit_env' => $nonEditEnv,
                'product_only_edit' => $productOnlyEdit,
                'opening_stock_editable' => $openingStockEditable,
                'used_opening_stock' => $usedOpeningStock,
                'specification'=>cleanInvisibleCharacters($data->specification),
                'size'=>cleanInvisibleCharacters($data->size)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'message' =>  $e->getMessage()
            ], 403);
        }
    }

    public static function getSortedUniqueCategoryNames()
    {
        return Category::where('status', 1)
            ->select('category_name')
            ->distinct()
            ->orderBy('category_name', 'asc')
            ->pluck('category_name');
    }

    //-----------------INVENTORY LIST & STOCK LEDGER REPORT ----------------------------------------------------
    public function getData(Request $request)
    {
        if (!$request->ajax()) return;

        $query = $this->applyFilters($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();
       
        $data1 = Route::currentRouteName() === 'buyer.report.stockLedger.listdata'
            ? $this->fetchStockledgerData($inventories)
            : $this->formatInventoryData($inventories);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data1,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }

    public function exportTotalInventoryData(Request $request)
    {
        $query = $this->applyFilters($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatchInventoryData(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = $this->applyFilters($request);
        $results = $query->offset($offset)->limit($limit)->get();
        $result = [];
        $inventoryIds =  (clone $results)->pluck('id')->toArray();
        $quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        $this->preloadGrnData($inventoryIds);
        $this->preloadRfqData($inventoryIds);
        $this->preloadOrderData($inventoryIds);
        $currency = session('user_currency')['symbol'] ?? '₹';
        foreach ($results as $index => $inv) {
            $indentQty = $inv->indents->where('is_deleted', 2)->where('closed_indent', 2)->sum('indent_qty');
            $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$quantityMaps);
            $rfq_qty = $this->getRfqData($inv->id)['rfq_qty'][$inv->id] ?? 0;
            $order_qty = $this->getOrderData($inv->id)['order_qty'][$inv->id] ?? 0;
            $grn_qty = $this->getGrnData($inv->id)['grn_qty'][$inv->id] ?? 0;

            $formattedRFQQty = $rfq_qty > 0
                ? NumberFormatterHelper::formatQty($rfq_qty, $currency)
                : 0;
            $formattedOrderQty = $order_qty > 0
                ? NumberFormatterHelper::formatQty($order_qty, $currency)
                : 0;
            $formattedGrnQty = $grn_qty > 0
                ? NumberFormatterHelper::formatQty($grn_qty, $currency)
                : 0;
                $result[] = [
                    $inv->branch->name ?? '',
                    $inv->item_code,
                    $inv->product->product_name ?? $inv->buyer_product_name,
                    $inv->product->category->category_name ?? '',
                    $inv->buyer_product_name,
                    cleanInvisibleCharacters($inv->specification),
                    cleanInvisibleCharacters($inv->size),
                    $inv->product_brand,
                    $inv->inventory_grouping,
                    " ".NumberFormatterHelper::formatQty($currentStockValue, $currency),
                    $inv->uom->uom_name ?? '',
                    " ".NumberFormatterHelper::formatQty($indentQty, $currency),
                    " ".$formattedRFQQty,
                    " ".$formattedOrderQty,
                    " ".$formattedGrnQty,
                ];

        }
        return response()->json(['data' => $result]);
    }

    public function applyFilters($request): Builder
    {
        // update join to left join
        $query = Inventories::query()
                ->leftJoin('products', 'products.id', '=', 'inventories.product_id')
                ->with([
                    'product:id,product_name,category_id',
                    'product.category:id,category_name',
                    'branch:branch_id,name',
                    'uom:id,uom_name',
                    'indentRfqs','indents'
                ])
                ->addSelect([
                    'inventories.id','inventories.buyer_branch_id','inventories.product_id','inventories.buyer_product_name','inventories.specification','inventories.size','inventories.product_brand','inventories.inventory_grouping','inventories.inventory_type_id','inventories.uom_id','inventories.opening_stock','inventories.indent_min_qty','item_code'
                ]);

        $user = Auth::user();
        $query->where('buyer_parent_id', $user->parent_id ?? $user->id);

        $query->when($request->filled('branch_id') && session('branch_id') != $request->branch_id, function () use ($request) {
            session(['branch_id' => $request->branch_id]);
        });

        $query->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->where('buyer_branch_id', $request->branch_id);
        });

        $query->when($request->filled('search_product_name'), function ($q) use ($request) {
            $q->where(function ($subQuery) use ($request) {
                $subQuery->where('buyer_product_name', 'like', '%' . $request->search_product_name . '%')
                    ->orwhere('specification', 'like', '%' . $request->search_product_name . '%')
                    ->orWhereHas('product', function ($q2) use ($request) {
                        $q2->where('product_name', 'like', '%' . $request->search_product_name . '%');
                    });
            });
        });

        $query->when($request->filled('search_category_id'), function ($q) use ($request) {
            $categoryIds = $this->getIdsByCategoryName($request->search_category_id);
            if (!empty($categoryIds)) {
                $q->whereHas('product.category', function ($q2) use ($categoryIds) {
                    $q2->whereIn('id', $categoryIds);
                });
            }
        });

        $query->when($request->filled('ind_non_ind'), function ($q) use ($request) {
            $indentMap = [2 => 1, 3 => 2];
            if (isset($indentMap[$request->ind_non_ind])) {
                $q->where('is_indent', $indentMap[$request->ind_non_ind]);
            }
        });
        //SEARCH BY ORDER NUMBER + ONLY PENDING GRN ITEMS
        $query->when($request->filled('search_order_no'), function ($q) use ($request,$user) {

            $pendingInventoryIds = $this->getPendingInventoryIds($request->search_order_no, $request->branch_id);

            $q->whereIn('inventories.id', $pendingInventoryIds);

        });
        //pending indent filter
        if ($request->filled('ind_non_ind') && $request->ind_non_ind == 4) {
            $indentIds=DB::table('indent')
                ->select('id')
                ->where('is_active', 1)
                ->where('closed_indent', 2)
                ->where('inv_status', 1)
                ->where('is_deleted', 2);
            $indentSumSub = DB::table('indent')
                ->select('inventory_id', DB::raw('SUM(indent_qty) as total_indent_qty'))
                ->where('is_active', 1)
                ->where('closed_indent', 2)
                ->where('inv_status', 1)
                ->where('is_deleted', 2)
                ->groupBy('inventory_id');

            $indentRfqSumSub = DB::table('indent_rfq')
                ->select('inventory_id', DB::raw('SUM(used_indent_qty) as total_used_indent_qty'))
                ->whereIn('indent_id', $indentIds)
                ->groupBy('inventory_id');

            $query->leftJoinSub($indentSumSub, 'indent_sum', function ($join) {
                    $join->on('indent_sum.inventory_id', '=', 'inventories.id');
                })
                ->leftJoinSub($indentRfqSumSub, 'rfq_sum', function ($join) {
                    $join->on('rfq_sum.inventory_id', '=', 'inventories.id');
                })
                ->where(function ($q) {
                    $q->whereRaw('ROUND(COALESCE(indent_sum.total_indent_qty, 0), 3) > ROUND(COALESCE(rfq_sum.total_used_indent_qty, 0), 3)')
                    ->whereRaw('COALESCE(indent_sum.total_indent_qty, 0) > 0');
                });
        }
        //unapproved indent filter
        if ($request->filled('ind_non_ind') && $request->ind_non_ind == 5) {
            $unapprovedindent = DB::table('indent')
                ->select('inventory_id')
                ->where('is_active', 2)
                ->where('closed_indent', 2)
                ->where('inv_status', 1)
                ->where('is_deleted', 2);
            $query->joinSub($unapprovedindent, 'unapprove_indent', function ($join) {
                    $join->on('unapprove_indent.inventory_id', '=', 'inventories.id');
                });
        }
        
        $query->when($request->filled('search_inventory_type_id'), function ($q) use ($request) {
            $q->where('inventory_type_id', $request->search_inventory_type_id);
        });

        $query->orderBy('products.product_name', 'asc')
               ->orderBy('inventories.created_at', 'desc');

        return $query;
    }

    protected function getPendingInventoryIds($search, $branch_id)
    {
        $userParentId = getParentUserId();

        $manualOrder = DB::table('manual_order_products as mop')
            ->select(
                'mop.inventory_id',
                'mop.product_quantity as qty',
                'mo.id as order_id',
                'mo.manual_po_number as po_number',
                DB::raw('4 as grn_type')
            )
            ->join('manual_orders as mo', 'mo.id', '=', 'mop.manual_order_id')
            ->join('inventories as inv', 'inv.id', '=', 'mop.inventory_id')
            ->where('mo.manual_po_number', 'LIKE', "%$search%")
            ->where('inv.buyer_parent_id', $userParentId)
            ->where('mo.order_status', '1')
            ->where('inv.buyer_branch_id', $branch_id);

        $rfqOrder = DB::table('rfq_product_variants as rpv')
            ->select(
                'rpv.inventory_id',
                'ov.order_quantity as qty',
                'o.id as order_id',
                'ov.po_number as po_number',
                DB::raw('1 as grn_type')
            )
            ->join('rfqs as r', function ($join) {
                $join->on('r.rfq_id', '=', 'rpv.rfq_id')
                    ->whereIn('r.buyer_rfq_status', [5, 8, 9, 10]);
            })
            ->join('order_variants as ov', function ($join) {
                $join->on('ov.rfq_id', '=', 'rpv.rfq_id')
                    ->on('ov.rfq_product_variant_id', '=', 'rpv.id');
            })
            ->join('orders as o', function ($join) {
                $join->on('ov.rfq_id', '=', 'o.rfq_id')
                    ->on('ov.po_number', '=', 'o.po_number');
            })
            ->join('inventories as inv', 'inv.id', '=', 'rpv.inventory_id')
            ->where('rpv.inventory_status', 1)
            ->where('inv.buyer_parent_id', $userParentId)
            ->where('inv.buyer_branch_id', $branch_id)
            ->where('o.order_status', '1')
            ->where('ov.po_number', 'LIKE', "%$search%");

        // ---- FIXED union (only ONCE) ----
        $unionOrder = $manualOrder->unionAll($rfqOrder)->get();

        $pendingInventoryIds = [];

        foreach ($unionOrder as $order) {

            $grnData = DB::table('grns')
                ->select(DB::raw('SUM(grn_qty) as total_grn'))
                ->where('inventory_id', $order->inventory_id)
                ->where('order_id', $order->order_id)
                ->where('grn_type', $order->grn_type)
                ->first();

            $totalGrn = $grnData->total_grn ?? 0;

            if (($order->qty - $totalGrn) > 0) {
                $pendingInventoryIds[] = $order->inventory_id;
            }
        }

        return array_values(array_unique($pendingInventoryIds));
    }

    private function fetchStockledgerData($inventories)
    {
        $inventories = collect($inventories);
        $inventoryIds = $inventories->pluck('id')->toArray();
        $quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        $this->preloadGrnData($inventoryIds);
        $this->preloadRfqData($inventoryIds);
        $this->preloadOrderData($inventoryIds);

        return $inventories->map(function ($inv)use ($quantityMaps) {
            $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$quantityMaps);
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            return [
                'product' => '<a href="' . route('buyer.report.productWiseStockLedger.index', ['id' => $inv->id]) . '" style="cursor: pointer; text-decoration: none;">' . ($inv->product->product_name ?? TruncateWithTooltipHelper::wrapTextSS($inv->buyer_product_name)) . '</a>',
                'category' => $inv->product->category->category_name ?? '',
                'our_product_name' => '<a href="' . route('buyer.report.productWiseStockLedger.index', ['id' => $inv->id]) . '" style="cursor: pointer; text-decoration: none;">' . (TruncateWithTooltipHelper::wrapTextSS($inv->buyer_product_name) ?? '') . '</a>',
                'specification' => TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($inv->specification)),
                'size' => TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($inv->size)),
                'brand' => TruncateWithTooltipHelper::wrapText($inv->product_brand),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($inv->inventory_grouping),
                'current_stock' => NumberFormatterHelper::formatQty($currentStockValue, $currencySymbol),
                'uom' => $inv->uom->uom_name ?? '',
            ];
        });
    }

    public function formatInventoryData($inventories)
    {
        $inventories = collect($inventories);
        $inventoryIds = $inventories->pluck('id')->toArray();
        $quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        $this->preloadGrnData($inventoryIds);
        $this->preloadRfqData($inventoryIds);
        $this->preloadOrderData($inventoryIds);

        $formatdata= $inventories->map(function ($inv) use ($quantityMaps) {
            $indentQty = $inv->indents->where('is_deleted', 2)->where('closed_indent', 2)->where('is_active', 1)->where('inv_status', 1)->sum('indent_qty');
            $unapproveIndentRow = Indent::where('inventory_id', $inv->id)->where('is_deleted', 2)->where('closed_indent', 2)->where('is_active', 2)->where('inv_status', 1)->count();

            $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$quantityMaps);
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            $showMinQty='';
            if(floatval(trim($inv->indent_min_qty)) && floatval(trim($currentStockValue)) < floatval(trim($inv->indent_min_qty))){
                $showMinQty= '<br><div style="position: relative; display: inline-block;">
                    <button class="ra-btn ra-btn-primary text-nowrap font-size-12 fw-normal px-2">
                        Min Qty
                    </button>
                    <span style="position: absolute; top: -4px; right: -20px; background-color: red; color: white; border-radius: 12px; padding: 2px 6px; font-size: 12px;">
                        '.NumberFormatterHelper::formatQty($inv->indent_min_qty,session('user_currency')['symbol'] ?? '₹').'
                    </span>
                </div>';

            }
            return [
                'select' => $this->generateSelectHtml($inv->id,$indentQty,$this->getRfqData($inv->id)['rfq_qty'][$inv->id] ?? 0,$unapproveIndentRow),
                // 'item_code' => $inv->item_code ?? '',
                'product' => !empty($inv->product->product_name)
                    ? '<a href="' . route('buyer.report.productWiseStockLedger.index', ['id' => $inv->id]) . '" style="cursor:pointer;text-decoration:none;">' . $inv->product->product_name . '</a>' . $showMinQty
                    : '',
                'category' => $inv->product->category->category_name ?? '',
                'our_product_name' => '<a href="' . route('buyer.report.productWiseStockLedger.index', ['id' => $inv->id]) . '" style="cursor:pointer;text-decoration:none;">' 
                    . TruncateWithTooltipHelper::wrapTextSS($inv->buyer_product_name) 
                    . '</a>' 
                    . (empty($inv->product->product_name) ? $showMinQty : ''),
                'specification' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($inv->specification.' '.$inv->size )),
                'size' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($inv->size)),
                'brand' => TruncateWithTooltipHelper::wrapText($inv->product_brand),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($inv->inventory_grouping),
                'current_stock' => NumberFormatterHelper::formatQty($currentStockValue, $currencySymbol),
                'uom' => $inv->uom->uom_name ?? '',
                'indent_qty' => '<span id="total_indent_qty_' . $inv->id . '">' . NumberFormatterHelper::formatQty($indentQty, $currencySymbol) . '</span>',
                'rfq_qty' => ($qty = $this->getRfqData($inv->id)['rfq_qty'][$inv->id] ?? 0) > 0
                    ? '<span onclick="activeRfqPopUP(' . $inv->id . ')" style="cursor:pointer;color:blue;" >'.NumberFormatterHelper::formatQty($qty, $currencySymbol).'</span>'
                    : 0,
                'order_qty' => ($qty = $this->getOrderData($inv->id)['order_qty'][$inv->id] ?? 0) > 0
                    ? '<span onclick="orderDetailsPopUP(' . $inv->id . ')" style="cursor:pointer;color:blue;" >'.NumberFormatterHelper::formatQty($qty, $currencySymbol).'</span>'
                    : 0,
                'grn_qty' => '<span onclick="grnPopUP(' . $inv->id . ')" style="cursor:pointer;color:blue;">' . NumberFormatterHelper::formatQty($this->getGrnData($inv->id)['grn_qty'][$inv->id] ?? 0, $currencySymbol) . '</span>',

            ];
        });
        $this->clearAllCacheSilent($inventoryIds);
        return $formatdata;
    }

    public function preloadGrnData(array $inventoryIds): void
    {
        $cacheKey = 'grn_data_' . md5(json_encode($inventoryIds));
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }
        $this->grnQtyCache = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($inventoryIds) {
            return Grn::whereIn('inventory_id', $inventoryIds)
                ->where('grn_type', 1)
                ->where('inv_status', 1)
                ->select('inventory_id', DB::raw('SUM(grn_qty) as total_grn_qty'))
                ->groupBy('inventory_id')
                ->pluck('total_grn_qty', 'inventory_id')
                ->toArray();
        });
    }

    public function getGrnData($inventoryId)
    {
        $grnQty = $this->grnQtyCache[$inventoryId] ?? 0;
        return [
            'grn_qty' => [$inventoryId => $grnQty]
        ];
    }

    public function preloadRfqData(array $inventoryIds): void
    {
        $cacheKey = 'rfq_data_' . md5(json_encode($inventoryIds));
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }
        $this->rfqDataCache = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($inventoryIds) {
            $result = [
                'already_fetch_rfq' => [],
                'close_rfq_id_arr' => [],
                'rfq_ids_against_inventory_id' => [],
                'rfq_qty' => [],
            ];

            $rfqs = Rfq::with([
                    'rfqProductVariants' => function ($q) use ($inventoryIds) {
                        $q->whereIn('inventory_id', $inventoryIds)
                        ->where('inventory_status', 1);
                    },
                    'rfqProductVariants.orderVariants' // for variant-level order info
                ])
                ->where('record_type', 2)
                ->whereHas('rfqProductVariants', function ($query) use ($inventoryIds) {
                    $query->whereIn('inventory_id', $inventoryIds)
                        ->where('inventory_status', 1);
                })
                ->get();

            foreach ($rfqs as $rfq) {
                $rfqId = $rfq->id;
                $status = $rfq->buyer_rfq_status;
                $result['already_fetch_rfq'][$rfqId] = $rfqId;
                $rfqProductVariants= $rfq->rfqProductVariants->where('inventory_status', 1);
                foreach ($rfqProductVariants as $variant) {
                    $inventoryId = $variant->inventory_id;
                    $quantity = $variant->quantity;
                    if($variant->inventory_status==1){
                        if (in_array($status, [8, 10])) {
                            $result['close_rfq_id_arr'][$rfqId] = $rfqId;
                            $result['rfq_ids_against_inventory_id'][$rfqId] = $inventoryId;
                            if ($status == 10 && $variant->orderVariantsActiveOrder->count() > 0) {
                                foreach ($variant->orderVariantsActiveOrder as $orderVariant) {
                                    $confirmedQty = (float) $orderVariant->order_quantity;

                                    if (!isset($result['rfq_qty'][$inventoryId])) {
                                        $result['rfq_qty'][$inventoryId] = 0;
                                    }
                                    $result['rfq_qty'][$inventoryId] += $confirmedQty;
                                }
                            }
                        } else {
                            if (!isset($result['rfq_qty'][$inventoryId])) {
                                $result['rfq_qty'][$inventoryId] = 0;
                            }
                            $result['rfq_qty'][$inventoryId] += $quantity;
                        }
                    }
                }
            }

            return $result;
        });
    }

    public function getRfqData($inventoryId): array
    {
        if (empty($this->rfqDataCache)) {
            return [];
        }

        $rfqQty = $this->rfqDataCache['rfq_qty'][$inventoryId] ?? 0;

        return [
            'rfq_qty' => [$inventoryId => $rfqQty],
        ];
    }

    public function preloadOrderData(array $inventoryIds): void
    {
        $cacheKey = 'order_data_' . md5(json_encode($inventoryIds));
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }
        $this->orderQtyCache = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($inventoryIds) {
            $totalQtyPerInventory = [];

            $variants = RfqProductVariant::with(['rfq.orders.order_variants'])
                ->whereIn('inventory_id', $inventoryIds)
                ->where('inventory_status', 1)
                ->whereHas('rfq', function ($query) {
                    $query->where('record_type', 2);
                })
                ->get();

            foreach ($variants as $variant) {
                $inventoryId = $variant->inventory_id;
                $productId = $variant->product_id;
                $rfq = $variant->rfq;

                foreach ($rfq->orders as $order) {
                    if ($order->order_status != 1) continue;

                    foreach ($order->order_variants as $ov) {
                        if ($ov->product_id == $productId && $ov->rfq_product_variant_id==$variant->id) {
                            if (!isset($totalQtyPerInventory[$inventoryId])) {
                                $totalQtyPerInventory[$inventoryId] = 0;
                            }
                            $totalQtyPerInventory[$inventoryId] += $ov->order_quantity;
                        }
                    }
                }
            }

            return $totalQtyPerInventory;
        });
    }

    public function getOrderData($inventoryId): array
    {
        $qty = $this->orderQtyCache[$inventoryId] ?? 0;
        return ['order_qty' => [$inventoryId => $qty]];
    }
    public function clearAllCacheSilent(array $inventoryIds = []): void
    {
        if (empty($inventoryIds)) return;

        $chunks = array_chunk($inventoryIds, $this->batchSize);

        foreach ($chunks as $chunk) {
            Cache::forget('grn_data_' . md5(json_encode($chunk)));
            Cache::forget('rfq_data_' . md5(json_encode($chunk)));
            Cache::forget('order_data_' . md5(json_encode($chunk)));

        }
    }

    protected function cacheRemember(string $key, \Closure $callback, int $minutes = 10)
    {
        return Cache::remember($key, now()->addMinutes($minutes), $callback);
    }

    //-----------------------------INVENTORY MIN QTY LOGIC------------------------

    private function generateSelectHtml($inventoryId, $indentQty, $rfqQty = 0, $unapproveIndentRow): string
    {
        $maxQty = $indentQty - $rfqQty;        
        $iconHtml = '<span data-toggle="collapse" style="cursor: pointer;display:none" id="minus_' . $inventoryId . '" class="pr-2 accordion_parent accordion_parent_' . $inventoryId . ' close_indent_tds" tab-index="' . $inventoryId . '"><i class="bi bi-dash-lg"></i></span><span data-toggle="collapse" style="cursor: pointer;" id="plus_' . $inventoryId . '" class="pr-2 accordion_parent accordion_parent_' . $inventoryId . ' open_indent_tds" tab-index="' . $inventoryId . '"><i class="bi bi-plus-lg"></i></span>';
       

        return $iconHtml . '
            <input type="checkbox" name="inv_checkbox[]" class="inventory_chkd" data-maxqty="' . $maxQty . '" id="inventory_id_' . $inventoryId . '" value="' . $inventoryId . '">';
    }

    public static function getIdsByCategoryName(string $name): array
    {
        $startingWith = Category::where('category_name', 'like', '{$name}%')
            ->where('status', 1)
            ->pluck('id')
            ->toArray();
        if (empty($startingWith)) {
            return Category::where('category_name', 'like', "%{$name}%")
                ->where('status', 1)
                ->pluck('id')
                ->toArray();
        }

        return $startingWith;
    }

    public function getInventoryDetails(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INDENT', 'add', '1');
            }
            $inventoryIds = $request->input('inventory_ids');

            if (empty($inventoryIds) || !is_array($inventoryIds)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No inventory IDs provided!',
                ]);
            }

            $inventories = Inventories::select('inventories.*')
                    ->leftjoin('products', 'products.id', '=', 'inventories.product_id')
                    ->with(['product', 'uom'])
                    ->whereIn('inventories.id', $inventoryIds)
                    ->orderBy('products.product_name', 'asc') 
                    ->orderBy('inventories.created_at', 'desc')
                    ->orderBy('inventories.updated_at', 'desc')
                    ->get();



            if ($inventories->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No inventory records found!',
                ]);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Inventory details fetched successfully!',
                'data' => $inventories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    //--------------------------------------------CURRENT STOCK REPORT----------------
    public function currentStockGetData(Request $request)
    {
        if (!$request->ajax()) return;

        $query = $this->currentStockApplyFilters($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();

        $data1 = $this->formatCurrentStockData($inventories,$request,true);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data1,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
    public function exportTotalCurrentStockData(Request $request)
    {
        $query = $this->currentStockApplyFilters($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatchCurrentStockData(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = $this->currentStockApplyFilters($request);
        $results = $query->offset($offset)->limit($limit)->get();
        $result = [];
        $totalCurrentStockAmount =$totalIssuedAmount=$totalGrnAmount= 0;

        $inventories = collect($results);
        $inventoryIds = $inventories->pluck('id')->toArray();
        $from = null;
        $to = null;
        if ($request->filled(['from_date', 'to_date'])) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
            $openingqtyMap = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds,$from,null);
            $openingamountValMap = CurrentStockReportAmountHelper::preloadValueMaps($inventoryIds,$from,null);
        }
        $qtyMap = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds,$from,$to);
        $amountValMap = CurrentStockReportAmountHelper::preloadValueMaps($inventoryIds,$from,$to);
        $currentStockqtyMap = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds,null,$to);
        $currentStockamountValMap = CurrentStockReportAmountHelper::preloadValueMaps($inventoryIds,null,$to);
        $currency = session('user_currency')['symbol'] ?? '₹';
        foreach ($results as $index => $inv) {
            $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$currentStockqtyMap);
            $currentStockAmountValue = CurrentStockReportAmountHelper::calculateAmountValue($inv->id,$inv->opening_stock,$inv->stock_price,$currentStockamountValMap);
            if($from && $to){
                $openingValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$openingqtyMap);
                $openingAmountValue = CurrentStockReportAmountHelper::calculateAmountValue($inv->id,$inv->opening_stock,$inv->stock_price,$openingamountValMap);
            }
            $grnValue = $qtyMap['grn'][$inv->id] ?? 0;
            $grnAmountValue = $amountValMap['grn'][$inv->id] ?? 0;

            $issue_qty = $qtyMap['issue'][$inv->id] ?? 0;
            $issue_return_qty = $qtyMap['issue_return'][$inv->id] ?? 0;
            $total_issued_qty = $issue_qty - $issue_return_qty;

            $issue_amount = $amountValMap['issue'][$inv->id] ?? 0;
            $issue_return_amount = $amountValMap['issue_return'][$inv->id] ?? 0;
            $total_issued_amount = $issue_amount - $issue_return_amount;
            $totalCurrentStockAmount += round($currentStockAmountValue, 2);
            $totalIssuedAmount += round($total_issued_amount, 2); 
            $totalGrnAmount += round($grnAmountValue, 2); 
                $row = [
                    $offset + $index + 1,
                    $inv->branch->name ?? '',
                    $inv->product->product_name ?? $inv->buyer_product_name,
                    $inv->buyer_product_name,
                    cleanInvisibleCharacters($inv->specification),
                    cleanInvisibleCharacters($inv->size),
                    $inv->inventory_grouping,
                    $inv->uom->uom_name ?? '',
                    " ".NumberFormatterHelper::formatQty($currentStockValue, $currency),
                    " ".NumberFormatterHelper::formatCurrency($currentStockAmountValue, $currency),
                    ];

                if ($from && $to) {
                    $row[] = " " . NumberFormatterHelper::formatQty($openingValue, $currency);
                    $row[] = " " . NumberFormatterHelper::formatCurrency($openingAmountValue, $currency);
                }

                $row = array_merge($row, [
                    " ".NumberFormatterHelper::formatQty($total_issued_qty, $currency),
                    " ".NumberFormatterHelper::formatCurrency($total_issued_amount, $currency),
                    " ".NumberFormatterHelper::formatQty($grnValue, $currency),
                    " ".NumberFormatterHelper::formatCurrency($grnAmountValue, $currency),
                ]);
            $result[] = $row;

        }
        $result[] = [
                '', '', '', '', '', '', '', '','','','','','','',
            ];
        $result[] = [
                '', '', '', '', '', '', '', '', 
                'Total Stock Amount('.$currency.'):',
                " ".NumberFormatterHelper::formatCurrency($totalCurrentStockAmount,$currency),
                'Total Issued Amount('.$currency.'):',
                " ".NumberFormatterHelper::formatCurrency($totalIssuedAmount,$currency),
                'Total GRN Amount('.$currency.'):',
                " ".NumberFormatterHelper::formatCurrency($totalGrnAmount,$currency),
            ];
        return response()->json(['data' => $result]);
    }

    public function currentStockApplyFilters($request)
    {
        $userId = Auth::user()->parent_id ?: Auth::user()->id;

        $query = Inventories::query()
            ->leftjoin('products', 'products.id', '=', 'inventories.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('uoms', 'uoms.id', '=', 'inventories.uom_id')
            ->where('inventories.buyer_parent_id', $userId);

        $from = null;
        $to = null;
        if ($request->filled(['from_date', 'to_date'])) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
        }

        $grnQuery = "(SELECT inventory_id, SUM(grn_qty) AS total
                    FROM grns
                    WHERE company_id = {$userId}" .
                    ( $to ? " AND updated_at <='{$to}'" : "") . "
                    GROUP BY inventory_id) AS grn_sums";

        $issueQuery = "(SELECT inventory_id, SUM(qty) AS total
                        FROM issued
                        WHERE buyer_id = {$userId} AND is_deleted = 2" .
                        ( $to ? " AND updated_at <='{$to}'" : "") . "
                        GROUP BY inventory_id) AS issue_sums";

        $issueReturnQuery = "(SELECT inventory_id, SUM(qty) AS total
                            FROM issued_returns
                            WHERE buyer_id = {$userId} AND is_deleted = 2" .
                            ( $to ? " AND updated_at <='{$to}'" : "") . "
                            GROUP BY inventory_id) AS issue_return_sums";

        $stockReturnQuery = "(SELECT inventory_id, SUM(qty) AS total
                            FROM return_stocks
                            WHERE buyer_id = {$userId} AND is_deleted = 2" .
                            ( $to ? " AND updated_at <='{$to}'" : "") . "
                            GROUP BY inventory_id) AS stock_return_sums";

        $query->leftJoin(DB::raw($grnQuery), 'grn_sums.inventory_id', '=', 'inventories.id');
        $query->leftJoin(DB::raw($issueQuery), 'issue_sums.inventory_id', '=', 'inventories.id');
        $query->leftJoin(DB::raw($issueReturnQuery), 'issue_return_sums.inventory_id', '=', 'inventories.id');
        $query->leftJoin(DB::raw($stockReturnQuery), 'stock_return_sums.inventory_id', '=', 'inventories.id');

        $query->select(
            'inventories.id',
            'inventories.opening_stock',
            'inventories.stock_price',
            'inventories.specification',
            'inventories.size',
            'inventories.buyer_product_name',
            'inventories.inventory_grouping',
            'inventories.product_id',
            'inventories.uom_id',
            'inventories.buyer_branch_id',
            'inventories.buyer_parent_id',
            'inventories.is_indent',
            'inventories.inventory_type_id',
            'inventories.created_at',
            'inventories.updated_at',
            'products.product_name',
            DB::raw("
                inventories.opening_stock
                + COALESCE(grn_sums.total, 0)
                - COALESCE(issue_sums.total, 0)
                + COALESCE(issue_return_sums.total, 0)
                - COALESCE(stock_return_sums.total, 0)
                AS current_stock_value")
        );

        $query->when($request->filled('stock_qty'), function ($q) use ($request) {
            if ($request->stock_qty == '0') {
                $q->having('current_stock_value', '=', 0);
            } elseif ($request->stock_qty == '1') {
                $q->having('current_stock_value', '>', 0);
            }
        });

        if ($request->filled('branch_id') && session('branch_id') != $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $query->when($request->filled('branch_id'), fn($q) =>
            $q->where('buyer_branch_id', $request->branch_id)
        );

        $query->when($request->filled(['from_date', 'to_date']), function ($q) use ($request) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
            $to = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
            $q->where('inventories.created_at', '<=', $to);

        });

        $query->when($request->filled('search_product_name'), function ($q) use ($request) {
            $search = $request->search_product_name;
            $q->where(function ($sub) use ($search) {
                $sub->where('inventories.buyer_product_name', 'like', "%{$search}%")
                    ->orWhere('products.product_name', 'like', "%{$search}%");
            });
        });
        $query->when($request->filled('search_category_id'), function ($q) use ($request) {
            $categoryIds = $this->getIdsByCategoryName($request->search_category_id);
            $q->whereIn('categories.id', $categoryIds);
        });

        $query->when($request->filled('ind_non_ind'), function ($q) use ($request) {
            if ($request->ind_non_ind == 2) $q->where('is_indent', 1);
            if ($request->ind_non_ind == 3) $q->where('is_indent', 2);
        });

        $query->when($request->filled('search_inventory_type_id'), fn($q) =>
            $q->where('inventory_type_id', $request->search_inventory_type_id)
        );

        return $query->orderBy('products.product_name', 'asc')
                    ->orderBy('inventories.created_at', 'desc')
                    ->orderBy('inventories.updated_at', 'desc');
    }

    private function formatCurrentStockData($inventories,$request,$wrapText)
    {
        $inventories = collect($inventories);
        $inventoryIds = $inventories->pluck('id')->toArray();
        $from = null;
        $to = null;
        if ($request->filled(['from_date', 'to_date'])) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
        }
        $qtyMap = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds,$from,$to);
        $amountValMap = CurrentStockReportAmountHelper::preloadValueMaps($inventoryIds,$from,$to);
        $currentStockqtyMap = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds,null,$to);
        $currentStockamountValMap = CurrentStockReportAmountHelper::preloadValueMaps($inventoryIds,null,$to);

        return $inventories->map(function ($inv) use (&$serial_no,$wrapText,$qtyMap,$amountValMap, $currentStockqtyMap, $currentStockamountValMap) {

                $inv->specification=cleanInvisibleCharacters($inv->specification);
                $stockQty = StockQuantityHelper::calculateCurrentStockValue($inv->id, $inv->opening_stock, $currentStockqtyMap);
                $stockAmountVal = CurrentStockReportAmountHelper::
                                    calculateAmountValue($inv->id, $inv->opening_stock, $inv->stock_price, $currentStockamountValMap);
                $currency = session('user_currency')['symbol'] ?? '₹';
                $issue_qty = $qtyMap['issue'][$inv->id] ?? 0;
                $issue_return_qty = $qtyMap['issue_return'][$inv->id] ?? 0;
                $total_issued_qty = $issue_qty - $issue_return_qty;

                $issue_amount = $amountValMap['issue'][$inv->id] ?? 0;
                $issue_return_amount = $amountValMap['issue_return'][$inv->id] ?? 0;
                $total_issued_amount = $issue_amount - $issue_return_amount;
            return [
                'product_name' => $inv->product->product_name ?? TruncateWithTooltipHelper::wrapTextSS($inv->buyer_product_name),
                'our_product_name' => $wrapText ?
                                    TruncateWithTooltipHelper::wrapTextSS($inv->buyer_product_name) : $inv->buyer_product_name,
                'specification' => $wrapText ? TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($inv->specification)) : cleanInvisibleCharacters($inv->specification),
                'size' => $wrapText ? TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($inv->size)) : cleanInvisibleCharacters($inv->size),
                'inventory_grouping' => $wrapText ?
                                        TruncateWithTooltipHelper::wrapText($inv->inventory_grouping) : $inv->inventory_grouping,
                'uom' => $inv->uom->uom_name ?? '',
                'current_stock_quantity' => NumberFormatterHelper::formatQty($stockQty,$currency),
                'total_amount' => NumberFormatterHelper::formatCurrency($stockAmountVal, $currency),
                'issued_quantity' =>NumberFormatterHelper::formatQty($total_issued_qty, $currency),
                'issued_amount' =>NumberFormatterHelper::formatCurrency($total_issued_amount, $currency),
                'grn_quantity' => NumberFormatterHelper::formatQty($qtyMap['grn'][$inv->id] ?? 0, $currency),
                'grn_amount' => NumberFormatterHelper::formatCurrency($amountValMap['grn'][$inv->id] ?? 0, $currency),
            ];
        });
    }

    //--------------------Dead STOCK REPORT-------------------------
    public function deadStockGetData(Request $request)
    {
        if (!$request->ajax()) return;
        if (!$request->filled(['from_date', 'to_date'])) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'message' => 'No record found. from_date & to_date are required.'
            ]);
        }

        $query = $this->deadStockApplyFilters($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();

        $data1 = $this->formatDeadStockData($inventories,$request,true);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data1,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
    public function deadStockApplyFilters($request)
    {
        $userId = Auth::user()->parent_id ?: Auth::user()->id;

        $query = Inventories::with([
            'product:id,product_name,category_id',
            'product.category:id,category_name',
            'uom:id,uom_name'
        ])
        ->leftjoin('products', 'products.id', '=', 'inventories.product_id')
        ->where('inventories.buyer_parent_id', $userId);

        $from = null;
        $to = null;
        if ($request->filled(['from_date', 'to_date'])) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
        }

        $query->select(
            'inventories.id',
            'inventories.opening_stock',
            'inventories.stock_price',
            'inventories.specification',
            'inventories.size',
            'inventories.buyer_product_name',
            'inventories.inventory_grouping',
            'inventories.product_id',
            'inventories.uom_id',
            'inventories.buyer_branch_id',
            'inventories.buyer_parent_id',
            'inventories.is_indent',
            'inventories.inventory_type_id',
            'inventories.created_at',
            'inventories.updated_at',
            'products.product_name'
        );

        $query->whereNotIn('inventories.id', function ($sub) use ($userId, $from, $to) {
            $sub->from('issued')
                ->select('inventory_id')
                ->where('buyer_id', $userId)
                ->where('is_deleted', 2);
            if ($from && $to) {
                $sub->whereBetween('updated_at', [$from, $to]);
            }
        });

        $query->when($request->filled('stock_qty'), function ($q) use ($request) {
            if ($request->stock_qty == '0') $q->having('current_stock_value', '=', 0);
            elseif ($request->stock_qty == '1') $q->having('current_stock_value', '>', 0);
        });
        $query->when($request->filled('to_date'), function ($q) use ($request) {

            $to = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();

            $q->where('inventories.created_at', '<=', $to);
        });

        if ($request->filled('branch_id') && session('branch_id') != $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $query->when($request->filled('branch_id'), fn($q) =>
            $q->where('buyer_branch_id', $request->branch_id)
        );

        $query->when($request->filled('search_product_name'), function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('buyer_product_name', 'like', '%' . $request->search_product_name . '%')
                    ->orWhereHas('product', function ($q2) use ($request) {
                        $q2->where('product_name', 'like', '%' . $request->search_product_name . '%');
                    });
            });
        });

        $query->when($request->filled('search_category_id'), function ($q) use ($request) {
            $categoryIds = $this->getIdsByCategoryName($request->search_category_id);
            $q->whereHas('product.category', fn($qc) => $qc->whereIn('id', $categoryIds));
        });

        $query->when($request->filled('ind_non_ind'), function ($q) use ($request) {
            if ($request->ind_non_ind == 2) $q->where('is_indent', 1);
            if ($request->ind_non_ind == 3) $q->where('is_indent', 2);
        });

        $query->when($request->filled('search_inventory_type_id'), fn($q) =>
            $q->where('inventory_type_id', $request->search_inventory_type_id)
        );

        return $query->orderBy('products.product_name', 'asc')
                    ->orderBy('inventories.created_at', 'desc')
                    ->orderBy('inventories.updated_at', 'desc');
    }
    private function formatDeadStockData($inventories,$request,$wrapText)
    {
        $inventories = collect($inventories);
        $inventoryIds = $inventories->pluck('id')->toArray();

        $from = null;
        $to = null;
        if ($request->filled(['from_date', 'to_date'])) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
        }
        
        return $inventories->map(function ($inv) use (&$serial_no,$wrapText) {
            $inv->specification=cleanInvisibleCharacters($inv->specification);                
            return [
                'product_name' => $inv->product->product_name ?? TruncateWithTooltipHelper::wrapTextSS($inv->buyer_product_name),
                'our_product_name' => $wrapText ?
                                    TruncateWithTooltipHelper::wrapTextSS($inv->buyer_product_name) : $inv->buyer_product_name,
                'specification' => $wrapText ? TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($inv->specification)) : cleanInvisibleCharacters($inv->specification),
                'size' => $wrapText ? TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($inv->size)) : cleanInvisibleCharacters($inv->size),
                'inventory_grouping' => $wrapText ?
                                        TruncateWithTooltipHelper::wrapText($inv->inventory_grouping) : $inv->inventory_grouping,
                'uom' => $inv->uom->uom_name ?? '',
            ];
        });
    }
    public function exportTotalDeadStockData(Request $request)
    {
        if (!$request->filled(['from_date', 'to_date'])) {
            return response()->json(['total' => 0]);
        }
        $query = $this->deadStockApplyFilters($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    public function exportBatchDeadStockData(Request $request)
    {
        if (!$request->filled(['from_date', 'to_date'])) {
            return response()->json(['data' => []]);
        }
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));

        $query = $this->deadStockApplyFilters($request);

        $results = $query->offset($offset)->limit($limit)->get();
        $result = [];
        foreach ($results as $index => $inv) {
                $result[] = [
                    $offset + $index + 1,
                    $inv->branch->name ?? '',
                    $inv->product->product_name ?? $inv->buyer_product_name,
                    $inv->buyer_product_name,
                    cleanInvisibleCharacters($inv->specification),
                    cleanInvisibleCharacters($inv->size),
                    $inv->inventory_grouping,
                    $inv->uom->uom_name ?? '',
                ];

        }
        return response()->json(['data' => $result]);
    }
    //----- ---------------------Dead STOCK REPORT--------- --------
    //-------------------------------MIN QTY REPORT----------------
    public function minQtyGetData(Request $request)
    {
        if (!$request->ajax()) return;

        $query = $this->applyFiltersMinQty($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();

        $data1 = $this->formatMinQtyReportData($inventories);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data1,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
    public function exportTotalminQtyData(Request $request)
    {
        $query = $this->applyFiltersMinQty($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    public function exportBatchminQtyData(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));

        $query = $this->applyFiltersMinQty($request);

        $results = $query->offset($offset)->limit($limit)->get();
        $inventoryIds =  (clone $results)->pluck('id')->toArray();
        $quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        $currency = session('user_currency')['symbol'] ?? '₹';
        $result = [];
        foreach ($results as $index => $inv) {
            $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$quantityMaps);

            $result[] = [
                    $inv->branch->name ?? '',
                    $inv->product->product_name ?? $inv->buyer_product_name,
                    $inv->buyer_product_name,
                    cleanInvisibleCharacters($inv->specification),
                    cleanInvisibleCharacters($inv->size),
                    " ".NumberFormatterHelper::formatQty($currentStockValue, $currency),
                    " ".NumberFormatterHelper::formatQty($inv->indent_min_qty, $currency),
                    optional($inv->updatedBy)->name ?? optional($inv->createdBy)->name ?? '',
                    $inv->updated_at?->format('d/m/Y') ?? $inv->created_at?->format('d/m/Y') ?? '',
                ];

        }
        return response()->json(['data' => $result]);
    }
    public function applyFiltersMinQty($request): Builder
    {
        /// update koin to left join 
        $query = Inventories::query()
                ->leftjoin('products', 'products.id', '=', 'inventories.product_id')
                ->with([
                    'product:id,product_name,category_id',
                    'product.category:id,category_name',
                    'branch:branch_id,name',
                    'uom:id,uom_name',
                    'indentRfqs','indents'
                ])
                ->addSelect([
                    'inventories.id','inventories.buyer_branch_id','inventories.product_id','inventories.buyer_product_name','inventories.specification','inventories.size','inventories.created_by','inventories.updated_by','inventories.created_at','inventories.updated_at','inventories.opening_stock','inventories.indent_min_qty',
                ]);

        $user = Auth::user();
        $query->where('buyer_parent_id', $user->parent_id ?? $user->id);

        $query->when($request->filled('branch_id') && session('branch_id') != $request->branch_id, function () use ($request) {
            session(['branch_id' => $request->branch_id]);
        });

        $query->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->where('buyer_branch_id', $request->branch_id);
        });

        $query->when($request->filled('search_product_name'), function ($q) use ($request) {
            $q->where(function ($subQuery) use ($request) {
                $subQuery->where('buyer_product_name', 'like', '%' . $request->search_product_name . '%')
                    ->orwhere('specification', 'like', '%' . $request->search_product_name . '%')
                    ->orWhereHas('product', function ($q2) use ($request) {
                        $q2->where('product_name', 'like', '%' . $request->search_product_name . '%');
                    });
            });
        });


        $query->when($request->filled('search_category_id'), function ($q) use ($request) {
            $categoryIds = $this->getIdsByCategoryName($request->search_category_id);
            if (!empty($categoryIds)) {
                $q->whereHas('product.category', function ($q2) use ($categoryIds) {
                    $q2->whereIn('id', $categoryIds);
                });
            }
        });
        $from = null;
        $to = null;
        if ($request->filled(['from_date', 'to_date'])) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
        }
        $query->when($from && $to, function ($q) use ($from, $to) {
            $q->whereBetween(DB::raw('DATE(COALESCE(inventories.updated_at, inventories.created_at))'), [$from, $to]);
        });


        $query->whereRaw('(inventories.indent_min_qty IS NOT NULL AND inventories.indent_min_qty <> "" AND inventories.indent_min_qty <> 0)')
              ->orderBy('products.product_name', 'asc')
              ->orderBy('inventories.created_at', 'desc')
              ->orderBy('inventories.updated_at', 'desc');

        return $query;
    }
    public function formatMinQtyReportData($inventories)
    {
        $inventories = collect($inventories);
        $inventoryIds = $inventories->pluck('id')->toArray();
        $quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);

        $formatdata= $inventories->map(function ($inv) use ($quantityMaps) {

            $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$quantityMaps);
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';

            return [
                'product_name' => $inv->product->product_name ?? TruncateWithTooltipHelper::wrapTextSS($inv->buyer_product_name),
                // 'category' => $inv->product->category->category_name ?? '',
                'our_product_name' => TruncateWithTooltipHelper::wrapTextSS($inv->buyer_product_name),
                'specification' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($inv->specification)),
                'size' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($inv->size)),
                'current_stock' => NumberFormatterHelper::formatQty($currentStockValue, $currencySymbol),
                'min_qty' => "<span class='editable-minQty' data-id='{$inv->id}' data-value='{$inv->indent_min_qty}''>".NumberFormatterHelper::formatQty($inv->indent_min_qty,$currencySymbol)."</span>",
                'added_by' => optional($inv->updatedBy)->name ?? optional($inv->createdBy)->name ?? '',
                'added_date' => $inv->updated_at?->format('d/m/Y') ?? $inv->created_at?->format('d/m/Y') ?? '',

            ];
        });
        $this->clearAllCacheSilent($inventoryIds);
        return $formatdata;
    }
    //-----------------------------MIN QTY REPORT----------------
    //---------------------------Delete Inventory---------------
    public function deleteInventory(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'delete', '1');
            }
            if (!$request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid request type.'
                ]);
            }

            $inventoryIds = $request->input('inventory_ids');

            if (empty($inventoryIds) || !is_array($inventoryIds)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Inventory not found'
                ]);
            }

            // Check for processed inventories
            $hasProcessedInventories = Inventories::whereIn('id', $inventoryIds)
                ->where(function ($query) {
                    $query->where('opening_stock', '>', 0)
                        ->orWhere('is_indent', 1);
                })
                ->exists();

            // Check if related manual orders or GRNs exist
            $hasManualOrders = ManualOrder::where('order_status', 1)
                ->whereHas('products', function ($query) use ($inventoryIds) {
                    $query->whereIn('inventory_id', $inventoryIds);
                })
                ->exists();

            $hasGrns = Grn::whereIn('inventory_id', $inventoryIds)->exists();

            if ($hasProcessedInventories || $hasManualOrders || $hasGrns) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Inventory already processed'
                ]);
            }

            // Delete the inventories
            $deleted = Inventories::whereIn('id', $inventoryIds)->delete();

            return response()->json([
                'status' => $deleted ? 1 : 0,
                'message' => $deleted ? 'Inventory deleted successfully' : 'Inventory not deleted, please try again later'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' =>  $e->getMessage()
            ],403);
        }
    }
    //--------------------------Reset Indent-------------------

    public function resetInventory(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INVENTORY_MANAGEMENT', 'edit', '1');
            }
            if (!$request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid request type.'
                ]);
            }

            $inventoryIds = $request->input('inventory_ids');

            if (empty($inventoryIds) || !is_array($inventoryIds)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Inventory not found.'
                ]);
            }

            // Check if GRN exists
            if (Grn::whereIn('inventory_id', $inventoryIds)->exists()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'GRN Already Processed.'
                ]);
            }

            // Check if Issued
            if (Issued::whereIn('inventory_id', $inventoryIds)->exists()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Issued Already Processed.'
                ]);
            }

            // Check if Return Stock exists
            if (ReturnStock::whereIn('inventory_id', $inventoryIds)->exists()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Stock Return Already Processed.'
                ]);
            }

            // Start Transaction
            try {
                DB::transaction(function () use ($inventoryIds) {
                    // Mark indent as deleted
                    Indent::whereIn('inventory_id', $inventoryIds)
                        ->update(['is_deleted' => 1]);
                    IndentRfq::whereIn('inventory_id', $inventoryIds)
                        ->delete();
                    // Reset RFQ's inventory_id
                    RfqProductVariant::whereIn('inventory_id', $inventoryIds)
                        ->update(['inventory_id' => null]);
                    Inventories::whereIn('id', $inventoryIds)->update(['is_indent' => '2']);
                });

                return response()->json([
                    'status' => 1,
                    'message' => 'Inventory reset successfully'
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Inventory not reset, please try again later',
                    'error' => $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' =>  $e->getMessage()
            ],403);
        }
    }

    //-----------------------Add RFQ------------------------------------
    public function fetchInventoryDetailsForAddRfq_old(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('GENERATE_NEW_RFQ', 'add', '1');
            }
            $inventoryIds = $request->input('inventories');
            $this->clearAllCacheSilent($inventoryIds);
            $this->preloadRfqData($inventoryIds);
            if (!$inventoryIds || !is_array($inventoryIds)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Inventory IDs are required',
                ]);
            }
            foreach ($inventoryIds as $id) {
                $inv = Inventories::with('indents')->find($id);
                if (!$inv) continue;

                $indentQty = $inv->indents->where('is_deleted', 2)->where('closed_indent', 2)->where('is_active', 1)->sum('indent_qty');
                $rfqQty = $this->getRfqData($id)['rfq_qty'][$id] ?? 0;

                $maxQty = $indentQty - $rfqQty;
                if ($maxQty <= 0) {
                    return response()->json([
                        'status' => 0,
                        'message' => "Any of the selected has no pending indent to add to RFQ."
                    ]);
                }
            }

            $inventories = Inventories::with(['product', 'uom'])
                ->whereIn('id', $inventoryIds)
                ->orderBy('created_at', 'desc')
                ->orderBy('updated_at', 'desc')
                ->get();

            $inventories = $inventories->sortBy(function ($inventory) {
                return $inventory->product ? strtolower($inventory->product->product_name) : '';
            })->values();

            if ($inventories->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No inventories found',
                ]);
            }
            $rfqs = Rfq::with('rfqProductVariants')
                ->where('record_type', 2)
                ->whereHas('rfqProductVariants', function ($query) use ($inventoryIds) {
                    $query->whereIn('inventory_id', $inventoryIds)->where('inventory_status', 1);
                })
                ->get();
            $rfqQuantities = [];
            foreach ($rfqs as $rfq) {
                $status = $rfq->buyer_rfq_status;
                if (!in_array($status, [8, 10])) {
                    foreach ($rfq->rfqProductVariants as $variant) {
                        $inventoryId = $variant->inventory_id;
                        $qty = $variant->quantity;

                        if (!isset($rfqQuantities[$inventoryId])) {
                            $rfqQuantities[$inventoryId] = 0;
                        }

                        $rfqQuantities[$inventoryId] += $qty;
                    }
                }
            }

            $data = $inventories->map(function ($inventory) use ($rfqQuantities) {
                $totalIndentQty = $inventory->indents()->sum('indent_qty');
                $rfqQty = $rfqQuantities[$inventory->id] ?? 0;
                $maxQty=$totalIndentQty - $rfqQty;
                return [
                    'prod_name' => $inventory->product ? $inventory->product->product_name : null,
                    'specification' => cleanInvisibleCharacters($inventory->specification),
                    'size' => cleanInvisibleCharacters($inventory->size),
                    'uom_name' => $inventory->uom ? $inventory->uom->uom_name : null,
                    'id' => $inventory->id,
                    'opening_stock' => $inventory->opening_stock,
                    'total_indent_qty' => NumberFormatterHelper::formatQty($maxQty,session('user_currency')['symbol'] ?? '₹'),
                    'maxQty' => $maxQty,
                ];
            });

            return response()->json([
                'status' => 1,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function fetchInventoryDetailsForAddRfq(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('GENERATE_NEW_RFQ', 'add', '1');
            }
            $inventoryIds = $request->input('inventories');
            if (!$inventoryIds || !is_array($inventoryIds)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Inventory IDs are required',
                ]);
            }

            $this->clearAllCacheSilent($inventoryIds);
            $this->preloadRfqData($inventoryIds);
            foreach ($inventoryIds as $id) {
                $inv = Inventories::with('indents')->find($id);
                if (!$inv) continue;

                $indentQty = $inv->indents->where('is_deleted', 2)->where('closed_indent', 2)->where('is_active', 1)->sum('indent_qty');
                $rfqQty = $this->getRfqData($id)['rfq_qty'][$id] ?? 0;
                $maxQty = $indentQty - $rfqQty;
                if ($maxQty <= 0) {
                    return response()->json([
                        'status' => 0,
                        'message' => "Any of the selected has no pending indent to add to RFQ."
                    ]);
                }
            }

            $inventories = Inventories::with(['product', 'uom','indents'])
                ->whereIn('id', $inventoryIds)
                ->orderBy('created_at', 'desc')
                ->orderBy('updated_at', 'desc')
                ->get();

            $inventories = $inventories->sortBy(function ($inventory) {
                return $inventory->product ? strtolower($inventory->product->product_name) : '';
            })->values();

            if ($inventories->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No inventories found',
                ]);
            }
            //dd($inventories);
            foreach ($inventories as $inventory) {
                if ($inventory->product_id == 0) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Before "ADD TO RFQ" please select Valid Ra-Procure product',
                    ]);
                }
            }


            $data = $inventories->map(function ($inventory) {
                $totalIndentQty = $inventory->indents()->sum('indent_qty');
                $rfqQty =$this->getRfqData($inventory->id)['rfq_qty'][$inventory->id] ?? 0;
                $maxQty=$totalIndentQty - $rfqQty;
                return [
                    'prod_name' => $inventory->product ? $inventory->product->product_name : null,
                    'specification' => cleanInvisibleCharacters($inventory->specification),
                    'size' => cleanInvisibleCharacters($inventory->size),
                    'uom_name' => $inventory->uom ? $inventory->uom->uom_name : null,
                    'id' => $inventory->id,
                    'opening_stock' => $inventory->opening_stock,
                    'total_indent_qty' => NumberFormatterHelper::formatQty($maxQty,session('user_currency')['symbol'] ?? '₹'),
                    'maxQty' => $this->formatNumberForInputQty($maxQty),
                ];
            });

            return response()->json([
                'status' => 1,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function formatNumberForInputQty($value){
        return is_numeric($value)?(intval($value)==$value?intval($value):number_format($value,3,'.','')):$value;
    }
    public function formatNumberForInput($value){
        return is_numeric($value)?(intval($value)==$value?intval($value):number_format($value,2,'.','')):$value;
    }
    public function generateRFQ(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('GENERATE_NEW_RFQ', 'add', '1');
            }
            $request->validate([
                'branch_id' => 'required|integer',
                'rfq_qty' => 'required|array|min:1',
                'rfq_qty.*' => 'required|numeric|min:0.001',
                'inventory_id' => 'required|array',
                'inventory_id.*' => 'required|integer|exists:inventories,id',
            ], [
                'rfq_qty.*.required' => 'Quantity is mandatory against each product.',
                'rfq_qty.*.min' => 'Quantity must be at least 0.001.',
                'inventory_id.*.exists' => 'Invalid inventory item selected.',
            ]);

            $rfq_draft_id = 'D' . time() . rand(1000, 9999);
            $company_id = Auth::user()->parent_id ?: Auth::user()->id;
            $current_user_id = Auth::user()->id;
            $inventoryProductId = Inventories::whereIn('id', $request->inventory_id)
                ->pluck('product_id')
                ->toArray();

            $blacklisted_vendors = DB::table('buyer_preferences')
                ->where('buyer_user_id', $company_id)
                ->where('fav_or_black', 2)
                ->pluck('vend_user_id')
                ->toArray();

            $uniqueProductId = DB::table('vendor_products as pv')
                ->join('products as p', 'p.id', '=', 'pv.product_id')
                // ->join('rfq_products as rp', 'rp.product_id', '=', 'pv.product_id')
                ->join('vendors as vp', 'pv.vendor_id', '=', 'vp.user_id')
                ->join('users as u', 'u.id', '=', 'vp.user_id')
                ->where('pv.vendor_status', 1)
                ->where('pv.edit_status', 0)
                ->where('pv.approval_status', 1)
                ->where('p.status', 1)
                ->whereNotNull('vp.vendor_code')
                ->where('u.status', 1)
                ->where('u.is_verified', 1)
                ->where('u.user_type', 2)
                ->whereIn('pv.product_id', $inventoryProductId)
                ->when(!empty($blacklisted_vendors), function ($q) use ($blacklisted_vendors) {
                    $q->whereNotIn('pv.vendor_id', $blacklisted_vendors);
                })
                ->select('pv.product_id')
                ->distinct()
                ->pluck('product_id')
                ->toArray();


            $failedProducts = array_diff(array_unique($inventoryProductId), $uniqueProductId);

            if (count($failedProducts) > 0) {

                // Get failed product names for better message
                $failedProductNames = DB::table('products')
                    ->whereIn('id', $failedProducts)
                    ->pluck('product_name')
                    ->toArray();

                return response()->json([
                    'status' => false,
                    'message' => "RFQ cannot be sent. " . count($failedProducts) .
                                " product(s) do not have any eligible vendor: " .
                                implode(", ", $failedProductNames)
                ]);
            }
            DB::beginTransaction();

            try {
                $inv_pnr = '';
                $ind_remarks = [];
                foreach ($request->inventory_id as $index => $inventoryId) {
                    // $indents = Indent::where('inv_status', 1)
                    //         ->where('is_deleted', 2)
                    //         ->where('is_active', 1)
                    //         ->where('inventory_id', $inventoryId)
                    //         ->get(['id', 'inventory_unique_id', 'inventory_id', 'remarks']);
                    $usedQtySubquery = DB::table('indent_rfq')
                        ->selectRaw('indent_id, SUM(used_indent_qty) as total_used_qty')
                        ->where('inventory_id', $inventoryId)
                        ->groupBy('indent_id');

                    $indents = Indent::query()
                        ->leftJoinSub($usedQtySubquery, 'used_rfq', function ($join) {
                            $join->on('used_rfq.indent_id', '=', 'indent.id');
                        })
                        ->where('indent.inv_status', 1)
                        ->where('indent.is_deleted', 2)
                        ->where('indent.is_active', 1)
                        ->where('indent.inventory_id', $inventoryId)
                        ->whereRaw('COALESCE(used_rfq.total_used_qty, 0) < indent.indent_qty')
                        ->get([
                            'indent.id',
                            'indent.inventory_unique_id',
                            'indent.inventory_id',
                            'indent.remarks',
                            'indent.indent_qty',
                            DB::raw('COALESCE(used_rfq.total_used_qty, 0) as total_used_qty')
                        ]);
                    // if ($indents->isNotEmpty()) {
                    //     $existing = $inv_pnr ? explode(',', $inv_pnr) : [];
                    //     $new = $indents->pluck('inventory_unique_id')->toArray();
                    //     $merged = array_merge($existing, $new);
                    //     sort($merged);
                    //     $inv_pnr = implode(',', $merged);

                    //     foreach ($indents as $indent) {
                    //         $ind_remarks[$indent->inventory_id][] = $indent->remarks;
                    //     }

                    // }
                    // Modify this function from Amit (3 Feb 2026)
                    $requiredQty = $request->rfq_qty[$index];
                    if ($indents->isNotEmpty()) {

                        $existing = $inv_pnr ? explode(',', $inv_pnr) : [];

                        $selectedIds = [];
                        $usedQty = 0;

                        foreach ($indents as $indent) {

                            if ($usedQty >= $requiredQty) {
                                break;
                            }

                            $availableQty = $indent->indent_qty - $indent->total_used_qty;

                            if ($availableQty <= 0) {
                                continue;
                            }

                            $selectedIds[] = $indent->inventory_unique_id;
                            $usedQty += $availableQty;

                            // remarks mapping
                            $ind_remarks[$indent->inventory_id][] = $indent->remarks;
                        }

                        $merged = array_merge($existing, $selectedIds);
                        sort($merged);
                        $inv_pnr = implode(',', $merged);
                    }
                }
                $rfq = new Rfq();
                $rfq->forceFill([
                    "rfq_id" => '',// $rfq_draft_id,
                    "buyer_id" => $company_id,
                    "buyer_user_id" => $current_user_id,
                    "buyer_branch" => $request->branch_id,
                    "record_type" => 1,
                    "is_bulk_rfq" => 2,
                    "buyer_rfq_status" => 1,
                    "prn_no"=> $inv_pnr,

                ]);
                $rfq->save();
                $rfq->rfq_id = generateRFQDraftNumber($rfq->id);
                $rfq->save();

                /***:- create temp product id  -:***/
                $addedProductIds = [];
                $productOrderMap = [];

                $variantOrderMap = [];
                foreach ($request->inventory_id as $index => $inventoryId) {
                    $qty = $request->rfq_qty[$index];

                    // Load related data from inventory
                    $inventory = Inventories::with(['product', 'uom'])->findOrFail($inventoryId);
                    $product_id = $inventory->product_id;

                    // Set product_order for each unique product
                    if (!isset($productOrderMap[$product_id])) {
                        $productOrderMap[$product_id] = count($productOrderMap) + 1;
                    }

                    // Set variant_order for each variant of the same product
                    if (!isset($variantOrderMap[$product_id])) {
                        $variantOrderMap[$product_id] = 1;
                    } else {
                        $variantOrderMap[$product_id]++;
                    }

                    // Add RFQ product (only once per product)
                    if (!in_array($product_id, $addedProductIds)) {
                        RfqProduct::create([
                            "rfq_id" => $rfq->rfq_id,
                            "product_id" => $product_id,
                            "brand" => $inventory->product_brand ?? '',
                            "product_order" => $productOrderMap[$product_id],
                            "remarks" => !empty(array_filter($ind_remarks[$inventoryId] ?? []))
                                ? implode(',', array_filter($ind_remarks[$inventoryId]))
                                : '',
                        ]);
                        $addedProductIds[] = $product_id;
                    }
                    $variant = new RfqProductVariant();
                    $variant->forceFill([
                        "rfq_id" => $rfq->rfq_id,
                        "product_id" => $product_id,
                        "variant_order" => $variantOrderMap[$product_id],
                        "variant_grp_id" => now()->timestamp . mt_rand(10000, 99999),
                        "uom" => $inventory->uom_id ?? '',
                        "size" => cleanInvisibleCharacters($inventory->size ?? ''),
                        "quantity" => $qty,
                        "specification" => cleanInvisibleCharacters($inventory->specification ?? ''),
                        "inventory_id" => $inventoryId
                    ]);
                    $variant->save();
                    // $this->updateInventory($product_id,$inventory->specification,$inventory->size,$inventory->uom_id,$qty,$company_id,$request->branch_id,$rfq->rfq_id);
                }


                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Draft RFQ generated successfully.',
                    'rfq_id' => $rfq->rfq_id,
                    'url' => route('buyer.rfq.compose-draft-rfq', ['draft_id' => $rfq->rfq_id])
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                // Handle the error
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create RFQ Draft. ' . $e->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    //-----------------Active RFQ Details---------------------
    public function getActiveRfqDetails($inventoryId)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('SENT_RFQ', 'view', '1');
            }
            $rfqs = Rfq::with('rfqProductVariants')
                        ->whereHas('rfqProductVariants', function ($query) use ($inventoryId) {
                            $query->where('inventory_id', $inventoryId)->where('inventory_status', 1);
                        })
                        ->where('record_type', 2)
                        ->orderBy('updated_at', 'asc')
                        ->get();

            $rfqData = $rfqs->map(function ($rfq) use ($inventoryId) {
                $filteredVariants = $rfq->rfqProductVariants->where('inventory_id', $inventoryId);
                $totalQty = $filteredVariants->sum('quantity');
                return [
                    'rfq_no'         => $rfq->rfq_id,
                    'rfq_date'       => optional($rfq->updated_at)->format('d/m/Y'),
                    'rfq_closed'     => in_array($rfq->buyer_rfq_status, [8, 10]) ? 'Yes' : 'No',
                    'rfq_qty'        => NumberFormatterHelper::formatQty($totalQty,session('user_currency')['symbol'] ?? '₹'),
                    'rfq_id'         => $rfq->rfq_id,
                ];
            })->unique('rfq_id')->values();

            return response()->json([
                'status' => 1,
                'data' => $rfqData,
                'message' => 'RFQ Active Details Succesfully Fetched Against This Inventory!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to fetch RFQ details: ' . $e->getMessage()
            ], 500);
        }
    }
    //-----------------------------Order Details-------------------
    public function getOrderDetails($inventoryId)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('ORDERS_CONFIRMED_LISTING', 'view', '1');
            }

            $data = [];

            $rfqs = Rfq::with([
                    'rfqProductVariants' => function ($query) use ($inventoryId) {
                        $query->where('inventory_id', $inventoryId)->where('inventory_status', 1);
                    },
                    'orders' => function ($query) {
                        $query->where('order_status', 1);
                    },
                    'orders.order_variants',
                    'rfqProductVariants.orderVariantsActiveOrder',
                    'orders.vendor'
                ])
                ->where('record_type', 2)
                ->whereHas('rfqProductVariants', function ($query) use ($inventoryId) {
                    $query->where('inventory_id', $inventoryId);
                })
                ->get();

            foreach ($rfqs as $rfq) {
                foreach ($rfq->orders as $order) {
                    $orderNo = $order->po_number;
                    $orderDate = $order->created_at ? $order->created_at->format('d/m/Y') : null;
                    $vendorName = $order->vendor->legal_name ?? 'N/A';
                    $rfqNo = $rfq->rfq_id;

                    foreach ($order->order_variants as $ov) {
                        foreach ($rfq->rfqProductVariants as $variant) {
                            // if ($variant->product_id == $ov->product_id) {
                            if ($ov->inventory_id == $inventoryId || $ov->rfq_product_variant_id == $rfq->rfqProductVariants->pluck('id')->first()) {
                                $data[] = [
                                    'order_id'    => $order->id,
                                    'order_no'    => $orderNo,
                                    'rfq_no'      => $rfqNo,
                                    'order_date'  => $orderDate,
                                    'order_qty'   => $ov->order_quantity,
                                    'vendor_name' => $vendorName,
                                    // 'basePoUrl' => route('buyer.report.manualPO.orderDetails', ['id' => '__ID__']),
                                    'basePoUrl' => route('buyer.rfq.order-confirmed.view', ['id' => '__ID__']),
                                ];
                            }
                        }
                    }
                }
            }

            if (empty($data)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No orders found for this inventory.'
                ]);
            }

            return response()->json([
                'status' => 1,
                'data' => $data,
                'message' => 'Order details successfully fetched for this inventory.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to fetch order details: ' . $e->getMessage()
            ], 500);
        }
    }
    //------------------------Update Inventory--------------------------
    public function updateInventory($productId, $specification, $size, $uomId, $qty, $companyId, $branchId, $rfqId, $product_brand)
    {
        DB::beginTransaction();

        try {
            // $spec = trim($specification);
            // $sz   = trim($size);
            $spec = trim($specification);
            $sz   = trim($size);
            $lastInventory = Inventories::where('buyer_parent_id', $companyId)
                        ->where('buyer_branch_id', $branchId)
                        ->orderBy('inventory_unique_id', 'desc')
                        ->first();

            $nextInventoryId = $lastInventory ? $lastInventory->inventory_unique_id + 1 : 1;
            // Check or create inventory
            // $inventory = Inventories::firstOrCreate([
            //     'product_id'    => $productId,
            //     'specification' => $spec,
            //     'size'          => $sz,
            //     'buyer_branch_id'     => $branchId,
            //     'buyer_parent_id'    => $companyId,
            // ], [
            //     'uom_id'           => $uomId,
            //     'product_brand' => $product_brand,
            //     'product_name' => Product::where('id', $productId)->value('product_name') ?? '',
            //     'inventory_unique_id' => $nextInventoryId,
            //     'opening_stock' => 0,
            //     'is_indent'     => 0,
            //     'created_by'    => Auth::user()->id,
            //     'created_at'    => now(),
            // ]);
            $inventory = Inventories::where('product_id', $productId)
                ->whereRaw('LOWER(specification) = ?', [strtolower($spec)])
                ->whereRaw('LOWER(size) = ?', [strtolower($sz)])
                ->where('buyer_branch_id', $branchId)
                ->where('buyer_parent_id', $companyId)
                ->first();
            if (!$inventory) {
                $inventory = Inventories::create([
                    'product_id'          => $productId,
                    'specification'       => $spec,
                    'size'                => $sz,
                    'buyer_branch_id'     => $branchId,
                    'buyer_parent_id'     => $companyId,
                    'uom_id'              => $uomId,
                    'product_brand'       => $product_brand,
                    'product_name'        => Product::where('id', $productId)->value('product_name') ?? '',
                    'inventory_unique_id' => $nextInventoryId,
                    'opening_stock'       => 0,
                    'is_indent'           => 0,
                    'created_by'          => Auth::id(),
                    'created_at'          => now(),
                ]);
            }
            //dd($inventory->id,$rfqId);
            //update inventory id
            $query = RfqProductVariant::where('product_id', $productId)
                ->where('rfq_id', $rfqId);

            if (empty($spec)) {
                $query->whereNull('specification');
            } else {
                $query->where('specification', $spec);
            }

            if (empty($sz)) {
                $query->whereNull('size');
            } else {
                $query->where('size', $sz);
            }

            $query->update([
                'inventory_id' => $inventory->id,
                'updated_at' => now()
            ]);



            //Get open RFQ quantity (status not in 8, 10)
            $openRfqQty = RfqProductVariant::where('inventory_id', $inventory->id)
                            ->where('inventory_status', 1)
                        // ->where('rfq_id', $rfqId)
                        ->whereHas('rfq', function ($q) {
                            $q->whereNotIn('buyer_rfq_status', [8, 10])
                            ->where('record_type', 2);
                        })
                        ->sum('quantity');
            // Get close RFQ with partial order quantity (status 10)
            $inventoryId = $inventory->id;
            $totalCloseRFQOrderQty = DB::table('rfqs')
                ->join('rfq_product_variants as rpv', 'rpv.rfq_id', '=', 'rfqs.rfq_id')
                ->join('orders', 'orders.rfq_id', '=', 'rfqs.rfq_id')
                ->join('order_variants', 'order_variants.po_number', '=', 'orders.po_number')
                // ->where('rfqs.rfq_id', $rfqId)
                ->where('rfqs.record_type', 2)
                ->where('rfqs.buyer_rfq_status', 10)
                ->where('rpv.inventory_id', $inventoryId)
                ->where('rpv.inventory_status', 1)
                ->where('orders.order_status', 1)
                ->whereColumn('order_variants.rfq_product_variant_id', 'rpv.id')
                ->sum(DB::raw('order_variants.order_quantity'));

            $openRfqQty += $totalCloseRFQOrderQty;

            // Get existing indent quantity
            $existingIndentQty = Indent::where('inventory_id', $inventory->id)
                ->where('closed_indent', 2)
                ->where('is_deleted', 2)
                ->where('is_active', 1)
                ->sum('indent_qty');


            // Insert indent if shortfall

            if ($existingIndentQty < $openRfqQty) {
                // $indentQty = $qty - $totalCommitted;
                $indentQty = $openRfqQty - $existingIndentQty;

                $maxIndentId = Indent::where('buyer_id', $companyId)->max('inventory_unique_id') ?? 0;
                Indent::create([
                    'buyer_id'          => $companyId,
                    'inventory_unique_id'   => $maxIndentId + 1,
                    'inventory_id'        => $inventory->id,
                    'closed_indent'        => 2,
                    'is_deleted'        => 2,
                    'is_active'          => 1,
                    'indent_qty'          => $indentQty,
                    'created_by'          => auth()->id(),
                    'updated_by'     => auth()->id(),
                    'updated_date'   => now(),
                ]);

                $inventory->update(['is_indent' => 1]);
            }

            //start indentRfQ
            $this->handleIndentRfqMapping($productId, $rfqId, $inventory);
            //end indentRfQ
            DB::commit();
            return $inventory->id;

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }

    // ==========================
    public function handleIndentRfqMapping($productId, $rfqId, $inventory)
    {
        $rfqLinksToDelete = IndentRfq::where('inventory_id', $inventory->id)
            ->where('rfq_id', $rfqId)
            ->get();

        if ($rfqLinksToDelete->isNotEmpty()) {
            IndentRfq::whereIn('id', $rfqLinksToDelete->pluck('id'))->delete();
        }

        DB::beginTransaction();
        try {
            $remainRfqQty = RfqProductVariant::where('product_id', $productId)
                ->where('rfq_id', $rfqId)
                ->where('inventory_id', $inventory->id)
                ->sum('quantity');
            $this->mapIndentToRfq($inventory->id, $rfqId, $remainRfqQty);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function mapIndentToRfq($inventoryId, $rfqId, $remainRfqQty)
    {
        $indents = Indent::where('inventory_id', $inventoryId)
            ->where('closed_indent', 2)
            ->where('is_deleted', 2)
            ->where('is_active', 1)
            ->get(['id', 'indent_qty']);

        foreach ($indents as $indent) {
            if ($remainRfqQty <= 0) break;

            $used = (float) (IndentRfq::where('indent_id', $indent->id)
                ->where('inventory_id', $inventoryId)
                ->sum('used_indent_qty') ?? 0);

            $remainIndentQty = $indent->indent_qty - $used;
            if ($remainIndentQty <= 0) continue;

            if ($remainIndentQty >= $remainRfqQty) {
                IndentRfq::create([
                    'indent_id' => $indent->id,
                    'rfq_id' => $rfqId,
                    'inventory_id' => $inventoryId,
                    'used_indent_qty' => $remainRfqQty
                ]);
                $remainRfqQty = 0;
            } else {
                IndentRfq::create([
                    'indent_id' => $indent->id,
                    'rfq_id' => $rfqId,
                    'inventory_id' => $inventoryId,
                    'used_indent_qty' => $remainIndentQty
                ]);
                $remainRfqQty -= $remainIndentQty;
            }
        }
    }

    public function handleClosedIndentRfq($rfqId): void
    {

        DB::transaction(function () use ($rfqId) {
            $rfq = Rfq::with(['rfqProductVariants.orderVariantsActiveOrder'])
                ->where('record_type', 2)
                ->where('rfq_id', $rfqId)
                ->first();

            if (!$rfq) {
                throw new \Exception("RFQ not found for ID: {$rfqId}");
            }

            $status = (int) $rfq->buyer_rfq_status;

            if ($status === 8) {
                IndentRfq::where('rfq_id', $rfqId)->delete();
                return;
            }

            if ($status === 10) {
                $variants = $rfq->rfqProductVariants->map(fn($v) => [
                    'variant_id' => $v->id,
                    'inventory_id' => $v->inventory_id,
                ]);

                if ($variants->isEmpty()) return;

                $orderQuantities = OrderVariant::select('rfq_product_variant_id', DB::raw('SUM(order_quantity) as total_order_qty'))
                    ->where('rfq_id', $rfqId)
                    ->whereHas('orderActive')
                    ->groupBy('rfq_product_variant_id')
                    ->pluck('total_order_qty', 'rfq_product_variant_id')
                    ->toArray();

                IndentRfq::where('rfq_id', $rfqId)->delete();

                foreach ($variants as $variant) {
                    $variantId = $variant['variant_id'];
                    $inventoryId = $variant['inventory_id'];
                    $remainRfqQty = (float) ($orderQuantities[$variantId] ?? 0);
                    if ($remainRfqQty > 0) {
                        $this->mapIndentToRfq($inventoryId, $rfqId, $remainRfqQty);
                    }
                }
            }
        });
    }
    //-------------------------------------------------------------------------Get Pass Order no search---------------------------------
    public function checkPoPending(Request $request)
    {
        $inventoryId = $request->input('inventory_id');
        $branchId = $request->input('branch_id');

        $buyerId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;

        if (Auth::user()->parent_id != 0) {
            $this->ensurePermission('GRN', 'add', '1');
        }        

        $result = [];
        $getPassController = app(GetPassController::class);

        $pendingManualOrder = $getPassController->getPendingOrderDetails($buyerId, $inventoryId, 'manual_order',null,null);
        $pendingRfqOrder = $getPassController->getPendingOrderDetails($buyerId, $inventoryId, 'rfq_order',null,null);

        $pendingManualOrderArray = $pendingManualOrder instanceof \Illuminate\Support\Collection ? $pendingManualOrder->toArray() : (is_array($pendingManualOrder) ? $pendingManualOrder : []);
        $pendingRfqOrderArray = $pendingRfqOrder instanceof \Illuminate\Support\Collection ? $pendingRfqOrder->toArray() : (is_array($pendingRfqOrder) ? $pendingRfqOrder : []);

        $pendingOrderArray = array_merge($pendingManualOrderArray, $pendingRfqOrderArray);
        $pendingOrders = !empty($pendingOrderArray) ? $pendingOrderArray : [];

        if (!empty($pendingOrders)) {
            $result[] = [
                'inventory_id'   => $inventoryId,
                'pending_orders' => $pendingOrders
            ];
        }
        

        if (empty($result)) {
            return response()->json([
                'status' => false,
                'message' => 'No pending GRN order found for this PO Number'
            ]);
        }

        return response()->json([
            'status' => true,
            'data'   => $result
        ]);
    }
    public function showProductNameList(Request $request){        
        $productName = $request->input('productName');
        $query = Inventories::query()
                ->leftJoin('products', 'products.id', '=', 'inventories.product_id')
                ->with([
                    'product:id,product_name,category_id',
                ])
                ->addSelect([
                    'inventories.id','inventories.buyer_branch_id','inventories.product_id','inventories.buyer_product_name','inventories.specification','inventories.size',
                ]);
        $query->when($request->filled('productName'), function ($q) use ($request) {
            $q->where(function ($subQuery) use ($request) {
                $subQuery->where('buyer_product_name', 'like', '%' . $request->productName . '%')
                    ->orwhere('specification', 'like', '%' . $request->productName . '%')
                    ->orWhereHas('product', function ($q2) use ($request) {
                        $q2->where('product_name', 'like', '%' . $request->productName . '%');
                    });
            });
        });
        $user = Auth::user();
        $query->where('buyer_parent_id', $user->parent_id ?? $user->id);

        $query->when($request->filled('branch_id') && session('branch_id') != $request->branch_id, function () use ($request) {
            session(['branch_id' => $request->branch_id]);
        });

        $query->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->where('buyer_branch_id', $request->branch_id);
        });
        $data=$query->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);

    }
    //----------------------------------Product Life Cycle----------------------------------------------------
    public function productLifeCycle(Request $request)
    {
        $ids = $request->input('inventory_ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['status' => false, 'message' => 'No Data Found']);
        }

        $inventories = Inventories::with('product')
            ->whereIn('id', $ids)
            ->select('id', 'product_id', 'specification', 'size', 'buyer_product_name')
            ->get();

        if ($inventories->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No Data Found']);
        }

        $finalData = [];

        foreach ($inventories as $inventory) {
            // 1️ Fetch order details
            $orderResponse = $this->getOrderDetailsForPLC($inventory->id);
            $rfq_orders = $orderResponse->getStatusCode() == 200 && $orderResponse->getData(true)['status'] == 1
                ? $orderResponse->getData(true)['data']
                : [];

            // 2️ Fetch indent details
            $indentResponse = $this->getIndentDetailsForPLC($inventory->id);
            $indent_details = $indentResponse->getStatusCode() == 200 && $indentResponse->getData(true)['status'] == true
                ? $indentResponse->getData(true)['data']
                : [];

            // 3 Fetch Grn details
            $grnResponse = $this->getGrnDetailsForPLC($inventory->id);
            $grn_details = $grnResponse->getStatusCode() == 200 && $grnResponse->getData(true)['status'] == true
                ? $grnResponse->getData(true)['data']
                : [];

            // 4 Fetch Issue details
            $issueResponse = $this->getIssueDetailsForPLC($inventory->id);
            $issue_details = $issueResponse->getStatusCode() == 200 && $issueResponse->getData(true)['status'] == true
                ? $issueResponse->getData(true)['data']
                : [];

            // 5 Fetch Issue details
            $consumeResponse = $this->getConsumeDetailsForPLC($inventory->id);
            $consume_details = $consumeResponse->getStatusCode() == 200 && $consumeResponse->getData(true)['status'] == true
                ? $consumeResponse->getData(true)['data']
                : [];

            $finalData[] = [
                'inventory_id'  => $inventory->id,
                'product_name'  => $inventory->product->product_name ?? $inventory->buyer_product_name,
                'specification' => $inventory->specification,
                'size'          => $inventory->size,
                'total_orders'  => count($rfq_orders),
                'orders'        => $rfq_orders,
                'indent_details'=> $indent_details ,
                'grn_details'   => $grn_details ,
                'issue_details'   => $issue_details ,
                'consume_details'   => $consume_details 
            ];
        }

        return response()->json(['status' => true, 'data' => $finalData]);
    }
    public function getOrderDetailsForPLC($inventoryId)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('ORDERS_CONFIRMED_LISTING', 'view', '1');
            }

            $data = [];

            // RFQ Orders
            $rfqs = Rfq::with([
                    'rfqProductVariants' => fn($q) => $q->where('inventory_id', $inventoryId),
                    'orders.order_variants',
                    'rfqProductVariants.orderVariantsActiveOrder',
                    'orders.vendor'
                ])
                ->where('record_type', 2)
                ->whereHas('rfqProductVariants', fn($q) => $q->where('inventory_id', $inventoryId))
                ->get();

            foreach ($rfqs as $rfq) {
                $variantId = $rfq->rfqProductVariants->pluck('id')->first();
                foreach ($rfq->orders as $order) {
                    foreach ($order->order_variants as $ov) {
                        if ($ov->inventory_id == $inventoryId || $ov->rfq_product_variant_id == $variantId) {
                             
                             $currency = $order->vendor_currency?? '₹';
                            $data[] = [
                                'order_id'    => $order->id,
                                'order_no'    => $order->po_number,
                                'rfq_no'      => $rfq->rfq_id,
                                'order_date'  => $order->created_at?->format('d/m/Y'),
                                'order_qty'   => NumberFormatterHelper::formatQty($ov->order_quantity, session('user_currency')['symbol'] ?? '₹'), 
                                'rate'        => NumberFormatterHelper::formatCurrency($ov->order_price,$currency),
                                'vendor_name' => $order->vendor->legal_name ?? 'N/A',
                                'basePoUrl'   => route('buyer.rfq.order-confirmed.view', ['id' => $order->id]),
                                'type'        => 'rfq',
                                'order_status'=> $order->order_status == '1' ? 'Confirm' : 'Cancel', //  corrected
                            ];
                        }
                    }
                }
            }

            // Manual Orders
            $manualOrders = ManualOrder::whereHas('products', fn($q) => $q->where('inventory_id', $inventoryId))
                ->with([
                    'products' => fn($q) => $q->where('inventory_id', $inventoryId),
                    'vendor'
                ])->get();

            foreach ($manualOrders as $mOrder) {
                foreach ($mOrder->products as $product) {
                    $currency = $mOrder->currencyDetails?->currency_symbol ?? '₹';
                    $data[] = [
                        'order_id'    => $mOrder->id,
                        'order_no'    => $mOrder->manual_po_number ?? 'N/A',
                        'rfq_no'      => '-',
                        'order_date'  => $mOrder->created_at?->format('d/m/Y'),
                        'order_qty'   => NumberFormatterHelper::formatQty($product->product_quantity, session('user_currency')['symbol'] ?? '₹'),
                        'rate'        => NumberFormatterHelper::formatCurrency($product->product_price,$currency),
                        'vendor_name' => $mOrder->vendor->legal_name ?? 'N/A',
                        'basePoUrl'   => route('buyer.report.manualPO.orderDetails', ['id' => $mOrder->id]),
                        'type'        => 'manual',
                        'order_status'=> $mOrder->order_status == '1' ? 'Confirm' : 'Cancel', //  corrected
                    ];
                }
            }

            // Sort by order_date descending
            usort($data, function($a, $b) {
                $dateA = \Carbon\Carbon::createFromFormat('d/m/Y', $a['order_date'] ?? now()->format('d/m/Y'));
                $dateB = \Carbon\Carbon::createFromFormat('d/m/Y', $b['order_date'] ?? now()->format('d/m/Y'));
                return $dateB <=> $dateA;
            });

            return empty($data)
                ? response()->json(['status' => 0, 'message' => 'No orders found for this inventory.'])
                : response()->json(['status' => 1, 'data' => $data, 'message' => 'Order details successfully fetched for this inventory.']);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to fetch order details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getIndentDetailsForPLC($inventoryId)
    {
        try {
            // Fetch indents for the given inventory
            $indents = Indent::with([
                'inventory.product',
                'inventory.uom',
                'inventory.branch',
                'updatedBy'
            ])
            ->where('inventory_id', $inventoryId)
            ->orderBy('created_at', 'desc')
            ->orderBy('inventory_unique_id', 'desc')
            ->get();

            if ($indents->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No indent data found for this inventory.'
                ]);
            }

            $data = $indents->map(function($indent) {
                // Status mapping
                if ($indent->is_deleted == 1) {
                    $status = 'Deleted';
                } elseif ($indent->closed_indent == 1) {
                    $status = 'Close Indent';
                } elseif ($indent->is_active == 1) {
                    $status = 'Approved';
                } elseif ($indent->is_active == 2) {
                    $status = 'Unapproved';
                } else {
                    $status = '-';
                }

                return [
                    'indent_number' => $indent->inventory_unique_id ?? '-',
                    'indent_qty'    => NumberFormatterHelper::formatQty($indent->indent_qty,session('user_currency')['symbol'] ?? '₹'),
                    'status'        => $status,
                    'added_date'    => $indent->created_at?->format('d/m/Y') ?? '-'
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $data,
                'message' => 'Indent data fetched successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch indent data: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getGrnDetailsForPLC($inventoryId)
    {
        try {
            $grns = Grn::with(['inventory', 'inventory.product'])
                ->where('inventory_id', $inventoryId)
                ->where(function($query) {
                    $query->where('grn_type', '!=', 3)
                        ->orWhere('order_id', '<>', 0)
                        ->orWhere('stock_return_for', '<>', 0);
                })
                ->orderBy('updated_at', 'desc')
                ->orderBy('grn_no', 'desc')
                ->get();

            if ($grns->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Grn data found for this inventory.'
                ]);
            }

            $data = $grns->map(function($grn) {
                if($grn->order_id == 0 && $grn->stock_return_for == 0){
                    $grn->reference_number = 'Stock Return No ' . optional($grn->stock)->stock_no;
                    $grn->rate = $grn->order_rate;
                }
                elseif ($grn->order_id == 0 && $grn->stock_return_for != 0) {
                    $grn->reference_number = 'Stock Return No ' . optional($grn->stock)->stock_no;

                    $originalGrnId = optional($grn->stock)->stock_return_for;
                    $originalGrn = Grn::with(['manualOrderProduct', 'order.order_variants', 'inventory'])->find($originalGrnId);

                    $grn->rate = $originalGrn ? $originalGrn->getOrderRateAttribute() : null;

                } else {
                    $grn->reference_number = $grn->po_number;
                    $grn->rate = $grn->order_rate;
                }

                return [
                    'grn_no' => $grn->grn_no ?? '-',
                    'grn_qty'    => NumberFormatterHelper::formatQty($grn->grn_qty, session('user_currency')['symbol'] ?? '₹'),
                    'grn_reference' => $grn->reference_number,
                    'rate' => NumberFormatterHelper::formatCurrency($grn->rate, session('user_currency')['symbol'] ?? '₹'),
                    'added_date' => $grn->updated_at?->format('d/m/Y') ?? '-'
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $data,
                'message' => 'Grn data fetched successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch grn data: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getIssueDetailsForPLC($inventoryId)
    {
        try {
            $issues = Issued::where('inventory_id', $inventoryId)->where('issued_return_for','<>','0')
            ->orderBy('issued_no', 'desc')->orderBy('updated_at', 'desc')->get();

            if ($issues->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Issue data found for this inventory.'
                ]);
            }

            $data = $issues->map(function($issue) {
                return [
                    'issued_no' => $issue->issued_no ?? '-',
                    'qty'    => NumberFormatterHelper::formatQty($issue->qty, session('user_currency')['symbol'] ?? '₹'),
                    'reference' => $issue->reference_number,
                    'rate' => NumberFormatterHelper::formatCurrency($issue->rate, session('user_currency')['symbol'] ?? '₹'),
                    'added_date' => $issue->updated_at 
                            ? Carbon::parse($issue->updated_at)->format('d/m/Y') 
                            : '-'
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $data,
                'message' => 'Issue data fetched successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch grn data: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getConsumeDetailsForPLC($inventoryId)
    {
        try {

            $consumes = Consume::with('issued')
                ->whereHas('issued', function ($query) use ($inventoryId) {
                    $query->where('inventory_id', $inventoryId)->where('issued_return_for', '<>', '0');
                })                
                ->orderBy('consume_no', 'desc')->orderBy('updated_at', 'desc')
                ->get();

            if ($consumes->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Consume data found for this inventory.'
                ]);
            }

            $data = $consumes->map(function ($consume) {

                $issued = $consume->issued;

                return [
                    'issued_no' => $issued->issued_no ?? '-',
                    'consume_no' => $consume->consume_no ?? '-',
                    'qty' => NumberFormatterHelper::formatQty(
                                $consume->qty, 
                                session('user_currency')['symbol'] ?? '₹'
                            ),
                    'reference' => $issued->reference_number ?? '-',
                    'issue_qty' =>  NumberFormatterHelper::formatQty(
                                $issued->qty, 
                                session('user_currency')['symbol'] ?? '₹'
                            ),
                    'rate' => NumberFormatterHelper::formatCurrency(
                                $issued->rate ?? 0, 
                                session('user_currency')['symbol'] ?? '₹'
                            ),
                    'added_date' => $consume->updated_at
                            ? Carbon::parse($consume->updated_at)->format('d/m/Y')
                            : '-'
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'Consume data fetched successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch consume data: ' . $e->getMessage()
            ], 500);
        }
    }
}
