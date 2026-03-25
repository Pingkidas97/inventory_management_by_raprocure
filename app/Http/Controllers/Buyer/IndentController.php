<?php

namespace App\Http\Controllers\Buyer;

use App\Exports\{
    closeIndentReportExport,IndentReportExport
};
use App\Helpers\{
    NumberFormatterHelper,TruncateWithTooltipHelper
};
use App\Http\Controllers\Controller;
use App\Models\{
    Indent,Inventories,Grn,Rfq,RfqProductVariant,IndentRfq
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Buyer\InventoryController;
use App\Http\Controllers\Buyer\GrnController;
use App\Http\Controllers\Buyer\InventoryPermissionCheckController;
use Carbon\Carbon;
use App\Services\ExportService;
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Traits\HasModulePermission;
use Illuminate\Support\Facades\DB;

class IndentController extends Controller
{
    use TrimFields;
    use HasModulePermission;
    protected $rfqDataCache = [];
    protected $batchSize = 100;
    public function __construct(protected ExportService $exportService) {}

    public function store(Request $request)
    {
        try {

            $is_active = (Auth::user()->parent_id != 0) ? '2' : '1';
            try {
                $request = $this->trimAndReturnRequest($request);

                $buyerId = Auth::user()->parent_id ?? Auth::user()->id;
                $userId = Auth::user()->id;

                if (!empty($request->indent_id)) {

                    if (Auth::user()->parent_id != 0) {
                        $InventoryPermissionCheckController = app(InventoryPermissionCheckController::class);
                        $hasPermissionIndentEdit = $InventoryPermissionCheckController
                            ->checkPermissionForInventorySectionController('INDENT', 'edit', '1');

                        if (!$hasPermissionIndentEdit) {
                            return response()->json(['status' => 0, 'message' => 'Unauthorized.'], 403);
                        }
                    }

                    $indentIds   = (array) $request->indent_id;
                    $inventoryIds = (array) $request->inventory_id;
                    $remarksArr   = (array) $request->remarks;
                    $qtyArr       = (array) $request->indent_qty;

                    foreach ($indentIds as $index => $indentId) {

                        $indent = Indent::find($indentId);

                        if (!$indent) {
                            return response()->json(['status' => false, 'message' => "Indent ID {$indentId} not found."], 422);
                        }

                        // 🔹 Determine active state
                        if ($indent->created_by == $userId && $indent->created_by == $buyerId && $indent->is_active == '1') {
                            $is_active = '1';
                        } elseif ($indent->created_by != $buyerId && $userId == $buyerId && $indent->is_active == '1') {
                            $is_active = '1';
                        } else {
                            $is_active = '2';
                        }

                        // 🔹 Prevent update if RFQ linked
                        $indentRfqExists = IndentRfq::where('indent_id', $indentId)->exists();
                        if ($indentRfqExists && Auth::user()->parent_id != 0) {
                            return response()->json([
                                'status' => false,
                                'message' => "Cannot update Indent Number {$indent->inventory_unique_id}, already linked with RFQ."
                            ], 422);
                        }

                        // 🔹 Safe remarks conversion
                        $remarks = $remarksArr[$index] ?? null;
                        if (is_array($remarks)) {
                            $remarks = implode(' ', $remarks);
                        }

                        $data = [
                            'inventory_id'  => $inventoryIds[$index] ?? null,
                            'indent_qty'    => $qtyArr[$index] ?? null,
                            'remarks'       => cleanInvisibleCharacters($remarks),
                            'buyer_id'      => $buyerId,
                            'inv_status'    => 1,
                            'is_active'     => $is_active,
                            'is_deleted'    => '2',
                            'closed_indent' => '2',
                            'approved_by_1' => ($is_active == 1) ? $userId : null,
                            'approved_by_2' => null
                        ];
                        // var_dump($data);die;

                        // 🔹 Validate each row
                        $validator = Validator::make($data, [
                            'indent_qty' => ['required', 'numeric', 'min:0.001', 'max:9999999999'],
                            'remarks'    => ['nullable', 'string', 'max:250'],
                        ]);

                        if ($validator->fails()) {
                            return response()->json([
                                'status' => false,
                                'message' => $validator->errors()->first()
                            ], 422);
                        }

                        // 🔹 Update indent
                        $indent->update($data);

                        // 🔹 Update RFQ qty sync
                        $this->updateIndentRfqOnIndentUpdate($indentId, $data['indent_qty']);
                        app(GrnController::class)->closeIndent($inventoryIds[$index]);
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'Indents updated successfully!'
                    ], 200);
                }
                if (Auth::user()->parent_id != 0) {
                    $this->ensurePermission('INDENT', 'add', '1');
                }
                $inventoryIds = $request->input('inventory_id');
                $indentQties  = $request->input('indent_qty');
                $remarksArr   = $request->input('remarks');

                if (empty($inventoryIds) || !is_array($inventoryIds)) {
                    return response()->json(['status' => false, 'message' => 'No inventory selected.'], 422);
                }

                $lastIndent = Indent::where('buyer_id', $buyerId)->orderByDesc('inventory_unique_id')->first();
                $nextInventoryId = $lastIndent ? $lastIndent->inventory_unique_id + 1 : 1;

                $now = now();
                $insertData = [];
                $updateInventoryIds = [];

                foreach ($inventoryIds as $index => $inventoryId) {
                    $qty = $indentQties[$index] ?? null;
                    $remarks = $remarksArr[$index] ?? null;

                    $validator = Validator::make([
                        'indent_qty' => $qty,
                        'remarks'    => $remarks,
                    ], [
                        'indent_qty' => ['required', 'numeric', 'min:0.001', 'max:9999999999', new NoSpecialCharacters(false)],
                        'remarks'    => ['nullable', 'string', 'max:250', new NoSpecialCharacters(true)],
                    ]);

                    if ($validator->fails()) {
                        return response()->json([
                            'status' => false,
                            'message' => $validator->errors()->first()
                        ], 422);
                    }

                    $insertData[] = [
                        'inventory_id'        => $inventoryId,
                        'indent_qty'          => $qty,
                        'remarks'             => cleanInvisibleCharacters($remarks),
                        'buyer_id'            => $buyerId,
                        'inventory_unique_id' => $nextInventoryId++,
                        'created_by'          => $userId,
                        //'updated_by'          => $userId,
                        'inv_status'          => 1,
                        'is_active'           => $is_active,
                        'is_deleted'          => '2',
                        'closed_indent'       => '2',
                        'created_at'          => $now,
                        'updated_at'          => $now,
                        'approved_by_1'       => ($is_active == 1) ? $userId : null,
                    ];

                    $updateInventoryIds[] = $inventoryId;
                }
                //dd($updateInventoryIds);
                if (!empty($insertData)) {
                    Indent::insert($insertData);
                    Inventories::whereIn('id', $updateInventoryIds)->update(['is_indent' => 1]);
                }

                return response()->json(['status' => true, 'message' => 'Indents saved successfully!'], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error saving Indent!',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function updateIndentRfqOnIndentUpdate($indentId, $newIndentQty)
    {
        $lastIndentRfq = IndentRfq::where('indent_id', $indentId)->latest('id')->first();

        if (!$lastIndentRfq) {
            return;
        }

        $rfqId = $lastIndentRfq->rfq_id;
        $inventoryId = $lastIndentRfq->inventory_id;

        $inventory = Inventories::find($inventoryId);

        if (!$inventory) {
            return;
        }
        $inventoryController = app(InventoryController::class);
        $inventoryController->handleIndentRfqMapping($inventory->product_id, $rfqId, $inventory);

    }


    public function destroy(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INDENT', 'delete', '1');
            }

            if (!$request->ajax()) {
                return response()->json([
                    'status' => 2,
                    'message' => 'Invalid request type.'
                ]);
            }

            // Normalize to arrays
            $indentIds    = (array) $request->indent_id;
            $inventoryIds = (array) $request->indent_inventory_id;
            $qtys         = (array) $request->indent_qty;

            // Validate after normalization
            $request->merge([
                'indent_id' => $indentIds,
                'indent_inventory_id' => $inventoryIds,
                'indent_qty' => $qtys,
            ]);

            $request->validate([
                'indent_id' => 'required|array',
                'indent_id.*' => 'integer|exists:indent,id',
                'indent_inventory_id' => 'required|array',
                'indent_inventory_id.*' => 'integer',
                'indent_qty' => 'required|array',
                'indent_qty.*' => 'numeric',
            ]);

            foreach ($indentIds as $index => $id) {

                $indent = Indent::with('inventory.branch')->find($id);
                if (!$indent) {
                    continue;
                }

                // qty mismatch protection
                if ($this->bccomp_fallback($indent->indent_qty, $qtys[$index], 3) !== 0) {
                    return response()->json([
                        'status' => 2,
                        // 'message' => "Qty mismatch for Indent ID {$id}."
                        'message' => 'Indent quantity mismatch. Stored Qty: '
                            . NumberFormatterHelper::formatQty($indent->indent_qty,session('user_currency')['symbol'] ?? '₹')
                            . ', Delete Request Qty: '
                            . NumberFormatterHelper::formatQty($qtys[$index],session('user_currency')['symbol'] ?? '₹')
                            . '. Please update quantity before deleting.'
                    ]);
                }

                // soft delete
                $indent->is_deleted = 1;
                $indent->save();

                $inventoryId = $inventoryIds[$index];

                // check other active indents
                $hasOtherIndents = Indent::where('inventory_id', $inventoryId)
                    ->where('is_deleted', '2')
                    ->exists();

                if (!$hasOtherIndents) {
                    Inventories::where('id', $inventoryId)
                        ->update(['is_indent' => '2']);
                }

                // close GRN
                app(GrnController::class)->closeIndent($inventoryId);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Indent deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function approve_amit_sir(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INDENT_APPROVE', 'add', '1');
            }

            if (!$request->ajax()) {
                return response()->json([
                    'status' => 2,
                    'message' => 'Invalid request type.'
                ]);
            }

            $userId  = Auth::id();
            $buyerId = Auth::user()->parent_id ?? $userId;

            // Normalize to arrays (handles single + bulk)
            $indentIds    = (array) $request->indent_id;
            $inventoryIds = (array) $request->indent_inventory_id;
            $qtys         = (array) $request->indent_qty;

            // Validate arrays
            $request->merge([
                'indent_id' => $indentIds,
                'indent_inventory_id' => $inventoryIds,
                'indent_qty' => $qtys,
            ]);

            $request->validate([
                'indent_id' => 'required|array',
                'indent_id.*' => 'integer|exists:indent,id',
                'indent_inventory_id' => 'required|array',
                'indent_inventory_id.*' => 'integer',
                'indent_qty' => 'required|array',
                'indent_qty.*' => 'numeric',
            ]);

            foreach ($indentIds as $index => $id) {

                $indent = Indent::with('inventory.branch')->find($id);
                if (!$indent) {
                    continue;
                }

                // Qty mismatch protection
                if ($this->bccomp_fallback($indent->indent_qty, $qtys[$index], 3) !== 0) {
                    return response()->json([
                        'status' => 2,
                        'message' => "Qty mismatch for Indent ID {$id}."
                    ]);
                }

                //  Approval logic
                $indent->updated_by = $userId;

                if ($indent->is_active == '2') {
                    $indent->approved_by_1 = $userId;
                } elseif (
                    $indent->is_active == '1' &&
                    $indent->approved_by_1 != null &&
                    $indent->approved_by_1 != $buyerId
                ) {
                    $indent->approved_by_2 = $userId;
                }

                $indent->is_active = '1';
                $indent->save();
            }

            return response()->json([
                'status' => 1,
                'message' => 'Indent approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function approve(Request $request, $id)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INDENT_APPROVE', 'add', '1');
            }
            if (!$request->ajax()) {
                return response()->json([
                    'status' => 2,
                    'message' => 'Invalid request type.'
                ]);
            }
            $userId = Auth::user()->id;
            $request->validate([
                'indent_inventory_id' => 'required|integer',
                'indent_qty' => 'required|numeric'
            ]);

            $indent = Indent::with('inventory.branch')->find($id);

            if (!$indent) {
                return response()->json([
                    'status' => 2,
                    'message' => 'Indent not found.'
                ]);
            }
            // qty mismatch
            if ($this->bccomp_fallback($indent->indent_qty, $request->indent_qty, 3) !== 0) {
                return response()->json([
                    'status' => 2,
                    'message' => 'Indent quantity mismatch. Stored Qty: '
                        . number_format($indent->indent_qty, 3)
                        . ', Approve Request Qty: '
                        . number_format($request->indent_qty, 3)
                        . '. Please update quantity before Approving.'
                ]);
            }
            // $indent->is_active = '1';
            // $indent->updated_by = $userId;
            // $indent->save();
            $indent->updated_by = $userId;
            $buyerId = Auth::user()->parent_id ?? Auth::user()->id;
            if($indent->is_active=='2'){
                $indent->approved_by_1 = $userId;
            }elseif($indent->is_active=='1' && $indent->approved_by_1 != null && $indent->approved_by_1 != $buyerId){
                $indent->approved_by_2 = $userId;
            }
            $indent->is_active = '1';

            $indent->save();

            $inventoryId = $request->indent_inventory_id;


            return response()->json([
                'status' => 1,
                'message' => 'Indent approved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function bulkApprove(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INDENT_APPROVE', 'add', '1');
            }

            if (!$request->ajax()) {
                return response()->json([
                    'status' => 2,
                    'message' => 'Invalid request type.'
                ]);
            }

            $userId  = Auth::id();
            $buyerId = Auth::user()->parent_id ?? $userId;

            $indentIds    = $request->input('indent_id');
            $inventoryIds = $request->input('indent_inventory_id');
            $qtys         = $request->input('indent_qty');

            $request->validate([
                'indent_id' => 'required|array',
                'indent_id.*' => 'integer|exists:indent,id',
                'indent_inventory_id' => 'required|array',
                'indent_inventory_id.*' => 'integer',
                'indent_qty' => 'required|array',
                'indent_qty.*' => 'numeric',
            ]);

            foreach ($indentIds as $index => $id) {

                $indent = Indent::with('inventory.branch')->find($id);
                if (!$indent) {
                    continue;
                }

                // Qty mismatch protection
                if ($this->bccomp_fallback($indent->indent_qty, $qtys[$index], 3) !== 0) {
                    return response()->json([
                        'status' => 2,
                        // 'message' => "Qty mismatch for Indent ID {$id}."
                        'message' => 'Indent quantity mismatch. Stored Qty: '
                            . NumberFormatterHelper::formatQty($indent->indent_qty, session('user_currency')['symbol'] ?? '₹')
                            . ', Approve Request Qty: '
                            . NumberFormatterHelper::formatQty($qtys[$index], session('user_currency')['symbol'] ?? '₹')
                            . '. Please update quantity before approving.'
                    ]);
                }

                //  Approval logic
                $indent->updated_by = $userId;

                if ($indent->is_active == '2') {
                    $indent->approved_by_1 = $userId;
                } elseif (
                    $indent->is_active == '1' &&
                    $indent->approved_by_1 != null &&
                    $indent->approved_by_1 != $buyerId
                ) {
                    $indent->approved_by_2 = $userId;
                }

                $indent->is_active = '1';
                $indent->save();
            }

            return response()->json([
                'status' => 1,
                'message' => 'Indent approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    protected function bccomp_fallback($left, $right, $scale = 2) {
        $left = round((float) $left, $scale);
        $right = round((float) $right, $scale);

        if ($left < $right) return -1;
        if ($left > $right) return 1;
        return 0;
    }
    public function fetchIndentData(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INDENT', 'view', '1');
            }
            $inventoryId = $request->inventory;

            if (!$inventoryId) {
                return response()->json(['status' => 0, 'message' => 'Invalid Inventory ID'], 400);
            }

            $indentData = Indent::with(['createdBy', 'updatedBy'])
                ->where('inventory_id', $inventoryId)
                ->where('is_deleted', 2)//pingki
                ->where('closed_indent', 2)//pingki
                ->where('inv_status', 1)
                ->get();

            if ($indentData->isEmpty()) {
                return response()->json(['status' => 0, 'message' => 'No Indent Found']);
            }

            $formattedData = $indentData->map(function ($indent) {
                $openEdit = '0';
                if (Auth::user()->parent_id == 0) {
                    $openEdit = '1';
                }else{
                    $InventoryPermissionCheckController = app(InventoryPermissionCheckController::class);
                    $hasPermissionIndentEdit = $InventoryPermissionCheckController->checkPermissionForInventorySectionController('INDENT', 'edit', '1');
                    $hasPermissionIndentApprove = $InventoryPermissionCheckController->checkPermissionForInventorySectionController('INDENT_APPROVE', 'add', '1');
                    if($hasPermissionIndentApprove){
                        $openEdit = '1';
                    }elseif($hasPermissionIndentEdit){
                        // if ($indent->is_active == 2 && $indent->created_by == Auth::user()->id) {
                            $openEdit = '1';
                        // }else{
                        //     $openEdit = '0';
                        // }
                    }else{
                        $openEdit = '0';
                    }
                }
                return [
                    'id' => $indent->id,
                    'is_active' => $indent->is_active,
                    'inventory_unique_id' => $indent->inventory_unique_id,
                    'indent_qty' => NumberFormatterHelper::formatQty($indent->indent_qty,session('user_currency')['symbol'] ?? '₹'),
                    'remarks' =>  cleanInvisibleCharacters($indent->remarks),
                    'created_at' => $indent->created_at,
                    'created_by' => optional($indent->createdBy)->name,
                    'approved_by_1' => optional($indent->approvedBy1)->name,
                    'approved_by_2' => optional($indent->approvedBy2)->name,
                    'updated_by' => optional($indent->updatedBy)->name,
                    'openEdit' => $openEdit,
                ];
            });
            $buyerId = Auth::user()->parent_id ?? Auth::user()->id;
            $numberOfIndentApprovalUser = $this->getNoOfIndentApprovedPermissionUser($buyerId);

            return response()->json(['status' => 1, 'resp' => $formattedData, 'numberOfIndentApprovalUser' => $numberOfIndentApprovalUser]);
            //return response()->json(['status' => 1, 'resp' => $formattedData]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function getNoOfIndentApprovedPermissionUser($buyerId)
    {
        $query =  DB::table('user_role_module_permissions as urmp')
        ->join('user_roles as ur', 'ur.id', '=', 'urmp.user_role_id')
        ->join('modules as m', 'm.id', '=', 'urmp.module_id')
        ->where('ur.user_master_id', $buyerId)
        ->where('m.module_slug', 'INDENT_APPROVE')
        ->where('urmp.can_add', 1)
        ->count();
        return $query;
    }

    public function getIndentData(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                // $this->ensurePermission('INDENT_APPROVE', 'add', '1');
                // $this->ensurePermission('INDENT', 'edit', '1');
                $InventoryPermissionCheckController = app(InventoryPermissionCheckController::class);
                $hasPermissionIndentEdit = $InventoryPermissionCheckController->checkPermissionForInventorySectionController('INDENT', 'edit', '1');
                $hasPermissionIndentApprove = $InventoryPermissionCheckController->checkPermissionForInventorySectionController('INDENT_APPROVE', 'add', '1');
                if(!$hasPermissionIndentApprove && !$hasPermissionIndentEdit){
                    return response()->json(['status' => 0, 'message' => 'Unauthorized.'], 403);
                }

            }
            $indentId = $request->indent_id;

            if (!$indentId) {
                return response()->json(['status' => 0, 'message' => 'Invalid Indent ID'], 400);
            }

            $indent = Indent::with(['inventory.product'])->where('id', $indentId)->where('is_deleted', 2)->where('closed_indent', 2)->where('inv_status', 1)->first();//pingki

            if (!$indent) {
                return response()->json(['status' => 0, 'message' => 'Indent not found']);
            }
            $inventoryId = $indent->inventory_id;
            $inventoryController = app(InventoryController::class);
            $inventoryController->preloadRfqData([$inventoryId]);
            $rfqData = $inventoryController->getRfqData($inventoryId);
            $rfqQty = $rfqData['rfq_qty'][$inventoryId] ?? 0;

            $restRfqQty = $rfqQty;
            $min_indent_qty=0;
            $allIndents = Indent::where('inventory_id', $inventoryId)
            ->where('is_deleted', 2)
            ->where('inv_status', 1)
            ->where('closed_indent', 2)
            ->orderBy('id')
            ->get(['id', 'indent_qty']);

            $showDelete = true;
            if ($indent->is_active == 2) {
                $showDelete = true;
            }else if ($rfqQty <= 0) {
                $showDelete = true;
            } else {
                //update code by pingki 18/3/26
                $min_indent_qty = IndentRfq::where('indent_id', $indentId)
                                ->where('inventory_id', $inventoryId)
                                ->sum('used_indent_qty');
                if($min_indent_qty>0){
                    $showDelete = false;
                }else{
                    $showDelete = true;
                }
                //update code by pingki 18/3/26
                // $showDelete = false;
                // $restRfqQty = $rfqQty;
                // $min_indent_qty = $rfqQty;
                // $lastRow_indent_qty=0;
                // foreach ($allIndents as $row) {
                //     // Added if condition by AMIT for Unused Indent should be delete if required
                //     $usedIndent = IndentRfq :: where('indent_id', $row->id)->get();
                //     if(count($usedIndent) == 0){
                //         $showDelete = true;
                //         $min_indent_qty=0;
                //         break;
                //     }else {
                //         if ($restRfqQty == 0) {
                //             $showDelete = true;
                //             $min_indent_qty=0;
                //             break;
                //         }
                //         if($row->is_active == '1'){
                //             if ($restRfqQty >= $row->indent_qty) {
                //                 if ($row->id == $indent->id) {
                //                     $min_indent_qty=$restRfqQty;
                //                     $showDelete = false;
                //                     break;
                //                 }
                //                 $lastRow_indent_qty=$row->indent_qty;
                //                 $restRfqQty -= $lastRow_indent_qty;

                //             } else if($restRfqQty > 0 && $restRfqQty < $row->indent_qty) {
                //                 $min_indent_qty = $restRfqQty;
                //                 $restRfqQty = 0;
                //                 if ($row->id == $indent->id) {
                //                     $showDelete = false;
                //                     break;
                //                 }
                //             }
                //         }
                //     }
                // }
            }
            $showApproveButton = '0';
            $countRowIndentRfq= IndentRfq::where('indent_id', $indent->id)->count();
            if($countRowIndentRfq > 0){
                $showApproveButton = '0';
            }else{
                if($indent->is_active == '2'){
                    if (Auth::user()->parent_id == 0) {
                        $showApproveButton = '1';
                    }else{
                        $InventoryPermissionCheckController = app(InventoryPermissionCheckController::class);
                        $hasPermission = $InventoryPermissionCheckController->checkPermissionForInventorySectionController('INDENT_APPROVE', 'add', '1');
                        if($hasPermission){
                            $showApproveButton = '1';
                        }else{
                            $showApproveButton = '0';
                        }
                    }
                }else{
                    $buyerId = Auth::user()->parent_id ?? Auth::user()->id;
                    $approved_by_1=$indent->approved_by_1;
                    $approved_by_2=$indent->approved_by_2;
                    if($approved_by_1!=null && $approved_by_2!=null){
                        $showApproveButton = '0';
                    }elseif($approved_by_1!=null && $approved_by_1 == $buyerId){
                        $showApproveButton = '0';
                    }else{
                        $numberOfIndentApprovalUser = $this->getNoOfIndentApprovedPermissionUser($buyerId);
                        if($numberOfIndentApprovalUser > 1){
                            $InventoryPermissionCheckController = app(InventoryPermissionCheckController::class);
                            $hasPermission = $InventoryPermissionCheckController->checkPermissionForInventorySectionController('INDENT_APPROVE', 'add', '1');
                            if($hasPermission && $approved_by_1 != Auth::user()->id){
                                $showApproveButton = '1';
                            }else{
                                $showApproveButton = '0';
                            }
                        }else{
                            $showApproveButton = '0';
                        }
                    }
                }
            }
            $data = [
                'id' => $indent->id,
                'inventory_id' => $indent->inventory_id,
                'inventory_unique_id' => $indent->inventory_unique_id,
                'indent_qty' => round($indent->indent_qty,3),
                'remarks' => cleanInvisibleCharacters($indent->remarks) ,
                'is_active' => $indent->is_active,
                'created_by' => $indent->created_by,
                'updated_by' => $indent->updated_by,
                'created_at' => $indent->created_at,
                'updated_at' => $indent->updated_at,
                'showDelete' => $showDelete,
                'min_indent_qty' => $min_indent_qty > 0 ? round($min_indent_qty,3) : 0,


                'product_name' => optional($indent->inventory->product)->product_name,
                'specification' => cleanInvisibleCharacters(optional($indent->inventory)->specification),
                'size' => cleanInvisibleCharacters(optional($indent->inventory)->size),
                'uom_id' => optional($indent->inventory)->uom_id,
                'uom_name' => optional($indent->inventory->uom)->uom_name,
                'showApproveButton' => $showApproveButton,
            ];

            return response()->json(['status' => 1, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    //-----------------------------------------INDENT REPORT-------------------------------------------------------------------
    public function fetchIndentReportDataFilter(Request $request)
    {
        $query = Indent::with(['inventory.product', 'inventory.uom','inventory.branch', 'updatedBy','inventory']);
        if (session('branch_id') != $request->branch_id) {
                session(['branch_id' => $request->branch_id]);
            }
        $query->where('buyer_id',Auth::user()->parent_id ?? Auth::user()->id)->where('closed_indent', 2);

        $query->when($request->branch_id, function ($q) use ($request) {
            $q->whereHas('inventory.branch', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        })
        // ->when($request->search_product_name, function ($q) use ($request) {
        //     $q->whereHas('inventory.product', function ($q) use ($request) {
        //         $q->where('product_name', 'like', "%{$request->search_product_name}%");
        //     });
        // })
        ->when($request->filled('search_product_name'), function ($q) use ($request) {
            $q->where(function ($subQuery) use ($request) {
                $subQuery->whereHas('inventory', function ($q1) use ($request) {
                    $q1->where('buyer_product_name', 'like', '%' . $request->search_product_name . '%');
                })
                    ->orWhereHas('inventory.product', function ($q2) use ($request) {
                        $q2->where('product_name', 'like', "%{$request->search_product_name}%");
                    });
            });
        })

        ->when($request->search_category_id, function ($q) use ($request) {
            $cat_id = InventoryController::getIdsByCategoryName($request->search_category_id);
            if (!empty($cat_id)) {
                $q->whereHas('inventory.product', function ($q) use ($cat_id) {
                    $q->whereIn('category_id', $cat_id);
                });
            }
        })
        ->when($request->search_is_active, function ($q) use ($request) {
            $q->where('is_active', $request->search_is_active);
        })
        ->when($request->from_date && $request->to_date, function ($q) use ($request) {
            $fromDate = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
            $toDate = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();
            $q->whereBetween('updated_at', [$fromDate, $toDate]);
        });


        return $query->orderBy('created_at', 'desc')->orderBy('inventory_unique_id', 'desc');
    }
    public function getindentreportData(Request $request)
    {
        if (!$request->ajax()) return;

        $filteredQuery = $this->fetchIndentReportDataFilter($request);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $filteredQuery->paginate($perPage, ['*'], 'page', $page);

        $indents = collect($paginated->items());
        $this->preloadIndentRfqData($indents);
        $data = $indents->map(function ($indent) {
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            $indent_status = ['1' => 'Approved', '2' => 'Unapproved'];
            $rfqQty = $this->rfqDataCache[$indent->id] ?? 0;
            if($indent->indent_qty >$rfqQty && $indent->is_deleted != '1'){
                $indentNumber =  '<input type="checkbox" name="inv_checkbox[]" class="inventory_chkd" id="inventory_id_' . $indent->inventory->id . '" data-indent-id="' . $indent->id . '" value='. $indent->inventory->id .' "> <span class="serial-no">'.$indent->inventory_unique_id.'</span>';
            }else{
                $indentNumber =  '<span class="serial-no">'.$indent->inventory_unique_id.'</span>';
            }
            return [
                'IndentNumber' => $indentNumber,
                'product' => $indent->inventory->product->product_name ?? $indent->inventory->buyer_product_name,
                'buyer_product_name' => $indent->inventory->buyer_product_name ?? '',
                'specification' => TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($indent->inventory->specification)),
                'size' => TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($indent->inventory->size)),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($indent->inventory->inventory_grouping),
                'users' => TruncateWithTooltipHelper::wrapText(optional($indent->updatedBy)->name),
                'indent_qty' => NumberFormatterHelper::formatQty($indent->indent_qty, $currencySymbol) . ($indent->is_deleted == '1' ? ' (Deleted)' : ''),
                'rfq_qty' => ($rfqQty) > 0
                                ? '<span onclick="activeIndentRfqPopUP(' . $indent->id . ')" style="cursor:pointer;color:blue;">'
                                    . NumberFormatterHelper::formatQty($rfqQty, $currencySymbol). '</span>'
                                : 0,

                'uom' => $indent->inventory->uom->uom_name ?? '',
                'remarks' => TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($indent->remarks)),
                'status' => $indent_status[$indent->is_active] ?? 'Unapproved',
                'updated_at' => Carbon::parse($indent->updated_at)->format('d/m/Y'),
            ];
        });
        $this->clearAllCacheSilent($indents);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
    public function preloadIndentRfqData($indents): void
    {
        // $this->clearAllCacheSilent($indents);
        $indentIds = collect($indents)->pluck('id')->toArray();

        $cacheKey = 'rfq_last_used_qty_' . md5(json_encode($indentIds));
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }
        $this->rfqDataCache = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($indentIds) {
            return IndentRfq::whereIn('indent_id', $indentIds)
                ->selectRaw('indent_id, SUM(used_indent_qty) as total_used_qty')
                ->groupBy('indent_id')
                ->pluck('total_used_qty', 'indent_id')
                ->map(fn ($qty) => (float) $qty)
                ->toArray();
        });
    }
    public function clearAllCacheSilent($indents): void
    {
        if (empty($indents)) return;
        $indentsArray = collect($indents)->toArray();
        $chunks = array_chunk($indentsArray, $this->batchSize);

        foreach ($chunks as $chunk) {
            Cache::forget('rfq_last_used_qty_' . md5(json_encode($chunk)));
        }
    }

    public function exportIndentreportData(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(3000);
        $currencySymbol = session('user_currency')['symbol'] ?? '₹';
        $filters = $request->only([
                'branch_id',
                'search_product_name',
                'search_category_id',
                'search_is_active',
                'from_date',
                'to_date',
            ]);

        $export = new IndentReportExport($filters, $currencySymbol);
        $fileName = 'Indent_Report_' . now()->format('d-m-Y') . '.xlsx';

        $response = $this->exportService->storeAndDownload($export, $fileName);

        return response()->json($response);
    }
    public function exportTotalIndentreportData(Request $request)
    {
        $query = $this->fetchIndentReportDataFilter($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatchIndentreportData(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = $this->fetchIndentReportDataFilter($request);
        $results = $query->offset($offset)->limit($limit)->get();
        $result = [];
        $currency = session('user_currency')['symbol'] ?? '₹';
        foreach ($results as $index => $indent) {
                $result[] = [
                    $indent->inventory_unique_id,
                    optional($indent->inventory->branch)->name ?? '',
                    optional($indent->inventory->product)->product_name ?? $indent->inventory->buyer_product_name,
                    $indent->inventory->buyer_product_name ?? '',
                    cleanInvisibleCharacters($indent->inventory->specification ?? ''),
                    cleanInvisibleCharacters($indent->inventory->size ?? ''),
                    $indent->inventory->inventory_grouping ?? '',
                    optional($indent->updatedBy)->name ?? '',
                    " ".NumberFormatterHelper::formatQty($indent->indent_qty, $currency) . ($indent->is_deleted == '1' ? ' (Deleted)' : ''),
                    optional($indent->inventory->uom)->uom_name ?? '',
                    $indent->remarks ?? '',
                    $indent->is_active == '1' ? 'Approved' : 'Unapproved',
                    optional($indent->updated_at)->format('d/m/Y'),
                ];

        }
        return response()->json(['data' => $result]);
    }
    //------------------------------------------------------Active RFQ Details---------------------------------------------------
    public function getActiveRfqDetails($indentId)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('SENT_RFQ', 'view', '1');
            }
            $rfqLinks = IndentRfq::where('indent_id', $indentId)->get(['inventory_id', 'rfq_id','used_indent_qty']);

            if ($rfqLinks->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No RFQ linked with this indent.'
                ]);
            }

            $rfqData = [];

            foreach ($rfqLinks as $link) {
                $rfq = Rfq::with(['rfqProductVariants' => function ($query) use ($link) {
                        $query->where('inventory_id', $link->inventory_id)
                            ->where('inventory_status', 1);
                    }])
                    ->where('rfq_id', $link->rfq_id)
                    ->where('record_type', 2)
                    ->first();

                if ($rfq) {
                    $totalQty = $rfq->rfqProductVariants->sum('quantity');

                    $rfqData[] = [
                        'rfq_no'     => $rfq->rfq_id,
                        'rfq_date'   => optional($rfq->updated_at)->format('d/m/Y'),
                        'rfq_closed' => in_array($rfq->buyer_rfq_status, [8, 10]) ? 'Yes' : 'No',
                        'rfq_qty'    => NumberFormatterHelper::formatQty($totalQty, session('user_currency')['symbol'] ?? '₹'),
                        'rfq_id'     => $rfq->rfq_id,
                        'used_indent_qty' => NumberFormatterHelper::formatQty($link->used_indent_qty, session('user_currency')['symbol'] ?? '₹'),
                    ];
                }
            }

            return response()->json([
                'status' => 1,
                'data' => collect($rfqData)->unique('rfq_id')->values(),
                'message' => 'RFQ Active Details Successfully Fetched Against This Inventory!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to fetch RFQ details: ' . $e->getMessage()
            ], 500);
        }
    }


    //---------------------------------------------CLOSE INDENT REPORT--------------------------------------------------------------
    public function closeIndentReportDataFilter(Request $request)
    {
        $query = Indent::with([
            'inventory.product',
            'inventory.branch',
            'inventory.uom',
            'inventory',
            'updatedBy'
        ])
        ->select('indent.*')
        ->join('inventories', 'indent.inventory_id', '=', 'inventories.id')
        ->join('products', 'inventories.product_id', '=', 'products.id')
        ->where('indent.closed_indent', 1)
        ->where('indent.is_deleted', 2)
        ->where('indent.buyer_id', Auth::user()->parent_id ?? Auth::user()->id);

        $query->when($request->search_category_id, function ($q) use ($request) {
            $cat_id = InventoryController::getIdsByCategoryName($request->search_category_id);
            if (!empty($cat_id)) {
                $q->whereHas('inventory.product', function ($q) use ($cat_id) {
                    $q->whereIn('category_id', $cat_id);
                });
            }
        });

        if (session('branch_id') != $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $query->when($request->branch_id, function ($q) use ($request) {
            $q->whereHas('inventory.branch', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        });

        // $query->when($request->search_product_name, function ($q) use ($request) {
        //     $q->whereHas('inventory.product', function ($q) use ($request) {
        //         $q->where('product_name', 'like', "%{$request->search_product_name}%");
        //     });
        // });
        $query->when($request->filled('search_product_name'), function ($q) use ($request) {
            $q->where(function ($subQuery) use ($request) {
                $subQuery->whereHas('inventory', function ($q1) use ($request) {
                    $q1->where('buyer_product_name', 'like', '%' . $request->search_product_name . '%');
                })
                ->orWhereHas('inventory.product', function ($q2) use ($request) {
                    $q2->where('product_name', 'like', "%{$request->search_product_name}%");
                });
            });
        });


        $query->when($request->from_date && $request->to_date, function ($q) use ($request) {
            $fromDate = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
            $toDate   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();

            $q->whereBetween('indent.updated_at', [$fromDate, $toDate]);
        });

        // $indents = $query->orderBy('updated_at', 'desc')->get();
        $indents = $query->orderBy('indent.updated_at', 'desc')->orderBy('products.product_name', 'asc')->get();
        $grouped = $indents->groupBy('inventory_id')->map(function ($group) {
            $first = $group->first();
            $first->indent_qty = $group->sum('indent_qty');
            return $first;
        });
        //dd($grouped->values());
        return $grouped->values();
    }


    public function getcloseindentreportData(Request $request)
    {
        if (!$request->ajax()) return;

        $filteredCollection = $this->closeIndentReportDataFilter($request);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;

        $paginated = new LengthAwarePaginator(
            $filteredCollection->forPage($page, $perPage),
            $filteredCollection->count(),
            $perPage,
            $page
        );

        $closeIndents = collect($paginated->items());

        $data = $closeIndents->map(function ($row) {
            $inventory = $row->inventory;
            $inventoryId = $row->inventory_id;


            $inventoryController = app(InventoryController::class);
            // $this->controller->preloadGrnData([$inventoryId]);
            // $this->controller->preloadRfqData([$inventoryId]);
            // $this->controller->preloadOrderData([$inventoryId]);
            $totalGrnQty = $this->getGrnData($inventoryId)['grn_qty'][$inventoryId] ?? 0;
            $totalRfqQty = $this->getRfqData($inventoryId)['rfq_qty'][$inventoryId] ?? 0;
            $totalOrderQty = $this->getOrderData($inventoryId)['order_qty'][$inventoryId] ?? 0;

            return [
                'details' => '<span data-toggle="collapse" style="cursor: pointer; display:none" id="minus_' . $inventoryId . '" class="pr-2 accordion_parent accordion_parent_' . $inventoryId . ' close_indent_tds" tab-index="' . $inventoryId . '"><i class="bi bi-dash-lg"></i></span>
                    <span data-toggle="collapse" style="cursor: pointer" id="plus_' . $inventoryId . '" class="pr-2 accordion_parent accordion_parent_' . $inventoryId . ' open_indent_tds" tab-index="' . $inventoryId . '"><i class="bi bi-plus-lg"></i></span>',
                'product' => $inventory->product->product_name ?? '',
                'buyer_product_name' => $inventory->buyer_product_name ?? '',
                'specification' => TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($inventory->specification)),
                'size' => TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($inventory->size)),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($inventory->inventory_grouping),
                'users' => $row->updatedBy->name ?? '',
                'uom' => $inventory->uom->uom_name ?? '',
                'indent_qty' => NumberFormatterHelper::formatQty($row->indent_qty, session('user_currency')['symbol'] ?? '₹'),
                'rfq_qty' => $totalRfqQty > 0 ? NumberFormatterHelper::formatQty($totalRfqQty, session('user_currency')['symbol'] ?? '₹') : 0,
                'order_qty' => $totalOrderQty > 0 ? NumberFormatterHelper::formatQty($totalOrderQty, session('user_currency')['symbol'] ?? '₹') : 0,
                'grn_qty' => $totalGrnQty > 0 ? NumberFormatterHelper::formatQty($totalGrnQty, session('user_currency')['symbol'] ?? '₹') : 0,
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }

    public function getGrnData($inventoryId)
    {
        $grnQtySum = Grn::where('inventory_id', $inventoryId)
            ->where('grn_type', 1)
            ->where('inv_status', 2)
            ->sum('grn_qty');

        return [
            'grn_qty' => [
                $inventoryId => $grnQtySum
            ]
        ];
    }
    public function getRfqData($inventoryIds): array
    {
        $result = [
            'already_fetch_rfq' => [],
            'close_rfq_id_arr' => [],
            'rfq_ids_against_inventory_id' => [],
            'rfq_qty' => [],
        ];
        $rfqs = Rfq::with('rfqProductVariants')
            ->where('record_type', 2)
            ->whereHas('rfqProductVariants', function ($query) use ($inventoryIds) {
                if (is_array($inventoryIds)) {
                    $query->whereIn('inventory_id', $inventoryIds)
                          ->where('inventory_status', 2);
                } else {
                    $query->where('inventory_id', $inventoryIds)
                          ->where('inventory_status', 2);
                }
            })
            ->get();
        foreach ($rfqs as $rfq) {
            $rfqId = $rfq->id;
            $status = $rfq->buyer_rfq_status;

            $result['already_fetch_rfq'][$rfqId] = $rfqId;
            $rfqProductVariants= $rfq->rfqProductVariants->where('inventory_status', 2);
            foreach ($rfqProductVariants as $variant) {
                $inventoryId = $variant->inventory_id;
                $quantity = $variant->quantity;

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
        return $result;
    }

    public function getOrderData($inventoryId): array
    {
        $totalQty = 0;

        $variants = RfqProductVariant::with(['rfq.orders.order_variants'])
            ->where('inventory_id', $inventoryId)
            ->where('inventory_status', 2)
            ->whereHas('rfq', function ($query) {
                $query->where('record_type', 2);
            })
            ->get();

        foreach ($variants as $variant) {
            $rfq = $variant->rfq;

            foreach ($rfq->orders as $order) {
                if ($order->order_status != 1) continue;

                foreach ($order->order_variants as $ov) {
                    if ($ov->product_id == $variant->product_id && $ov->rfq_product_variant_id==$variant->id) {
                        $totalQty += $ov->order_quantity;
                    }
                }
            }
        }

        return ['order_qty' => [$inventoryId => $totalQty]];
    }
    public function exportCloseIndentData(Request $request)
    {

        if ($request->ajax()) {
            ini_set('memory_limit', '2048M');
            set_time_limit(3000);
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            $filters = $request->only([
                    'branch_id',
                    'search_product_name',
                    'search_category_id',
                    'from_date',
                    'to_date',
                ]);

            $export = new closeIndentReportExport($filters, $currencySymbol);
            $fileName = 'Close_Indent_Report_' . now()->format('d-m-Y') . '.xlsx';

            $response = $this->exportService->storeAndDownload($export, $fileName);

            return response()->json($response);
        }
    }
    public function exportTotalcloseindentdata(Request $request)
    {
        $collection  = $this->closeIndentReportDataFilter($request);
        $total = $collection->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatchcloseindentdata(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $collection = $this->closeIndentReportDataFilter($request);
        $results = $collection->slice($offset, $limit)->values();
        $result = [];
        $currency = session('user_currency')['symbol'] ?? '₹';
        foreach ($results as $index => $indent) {
            $rfq_qty = $this->getRfqData($indent->inventory->id)['rfq_qty'][$indent->inventory->id] ?? 0;
            $order_qty = $this->getOrderData($indent->inventory->id)['order_qty'][$indent->inventory->id] ?? 0;
            $grn_qty = $this->getGrnData($indent->inventory->id)['grn_qty'][$indent->inventory->id] ?? 0;
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
                $offset + $index + 1,
                optional($indent->inventory->branch)->name ?? '',
                optional($indent->inventory->product)->product_name ?? '',
                $indent->inventory->buyer_product_name ?? '',
                cleanInvisibleCharacters($indent->inventory->specification ?? ''),
                cleanInvisibleCharacters($indent->inventory->size ?? ''),
                $indent->inventory->inventory_grouping ?? '',
                optional($indent->updatedBy)->name ?? '',
                optional($indent->inventory->uom)->uom_name ?? '',
                " ".NumberFormatterHelper::formatQty($indent->indent_qty, $currency) . ($indent->is_deleted == '1' ? ' (Deleted)' : ''),
                " ".$formattedRFQQty,
                " ".$formattedOrderQty,
                " ".$formattedGrnQty,
            ];

        }
        return response()->json(['data' => $result]);
    }
    public function closeindentdata(Request $request)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('INDENT', 'view', '1');
            }
            $inventoryId = $request->inventory;

            if (!$inventoryId) {
                return response()->json(['status' => 0, 'message' => 'Invalid Inventory ID'], 400);
            }

            $indentData = Indent::with(['createdBy', 'updatedBy'])
                ->select(
                    'id','is_active','inventory_unique_id','indent_qty','remarks','created_at','created_by', 'updated_by'
                )
                ->where('inventory_id', $inventoryId)
                ->where('closed_indent', 1)
                ->where('is_deleted', 2)
                ->get();

            if ($indentData->isEmpty()) {
                return response()->json(['status' => 0, 'message' => 'No Indent Found']);
            }

            $formattedData = $indentData->map(function ($indent) {
                return [
                    'id' => $indent->id,
                    'is_active' => $indent->is_active,
                    'inventory_unique_id' => $indent->inventory_unique_id,
                    'indent_qty' => NumberFormatterHelper::formatQty($indent->indent_qty, session('user_currency')['symbol'] ?? '₹'),
                    'remarks' => $indent->remarks,
                    'created_at' => $indent->created_at,
                    'created_by' => $indent->createdBy->name ?? null,
                    'updated_by' => $indent->updatedBy->name ?? null
                ];
            });

            return response()->json(['status' => 1, 'resp' => $formattedData]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function searchInventory(Request $request)
    {
        $search = $request->search;
        $branch_id = $request->branch_id;
        $buyerId = Auth::user()->parent_id ?? Auth::user()->id;

        $products = DB::table('inventories as i')
        ->leftJoin('uoms as u', 'u.id', '=', 'i.uom_id')
        ->select(
            'i.id',
            'i.product_name',
            'i.specification',
            'i.size',
            'i.buyer_product_name',
            'i.size',
            'i.uom_id',
            'u.uom_name'
        )
        ->where('i.buyer_parent_id', $buyerId)
        ->where('i.buyer_branch_id', $branch_id)
        ->where(function ($q) use ($search) {
            $q->where('i.product_name', 'like', "%{$search}%")
            ->orWhere('i.specification', 'like', "%{$search}%")
            ->orWhere('i.buyer_product_name', 'like', "%{$search}%");
        })
        ->limit(10)
        ->get();

        return response()->json($products);
    }

    public function getMultiIndentData(Request $request)
    {
        try {

            /** 🔹 Permission check */
            if (Auth::user()->parent_id != 0) {
                $permCtrl = app(InventoryPermissionCheckController::class);

                $canEdit    = $permCtrl->checkPermissionForInventorySectionController('INDENT', 'edit', '1');
                $canApprove = $permCtrl->checkPermissionForInventorySectionController('INDENT_APPROVE', 'add', '1');

                if (!$canEdit && !$canApprove) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Unauthorized.'
                    ], 403);
                }
            }

            /** 🔹 Validate ID */
            // $indentId = $request->indentId;
            $dataArray = $request->input('data_array');

            if (!$dataArray) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid Indent Data'
                ], 400);
            }

            /** 🔹 Handle single OR comma-separated IDs */
            // $indentIds = is_array($indentId)
            //     ? $indentId
            //     : array_filter(explode(',', $indentId));

            /** 🔹 Fetch MULTIPLE indents */
            // $indents = Indent::with(['inventory.product', 'inventory.uom'])
            //     ->whereIn('inventory_id', $indentIds)
            //     ->where('is_deleted', 2)
            //     ->where('closed_indent', 2)
            //     ->where('inv_status', 1)
            //     ->where('is_active', 2)
            //     ->get();
            $inventoryIds = [];
            $indentIds = [];

            foreach ($dataArray as $item) {
                if (isset($item['inventory_id'])) {
                    $inventoryIds[] = $item['inventory_id'];
                }
                if (isset($item['indent_id'])) {
                    $indentIds[] = $item['indent_id'];
                }
            }
            $adminBuyerId = Auth::user()->parent_id == 0 ? Auth::user()->id : Auth::user()->parent_id;
            $buyerId = Auth::user()->id;

            $indents = Indent::with(['inventory.product', 'inventory.uom'])
                ->whereIn('inventory_id', $inventoryIds)
                ->whereIn('id', $indentIds)
                ->whereNull('approved_by_2')
                ->where('is_deleted', 2)
                ->where('closed_indent', 2)
                ->where(function($q) use ($adminBuyerId, $buyerId) {
                    // 🔹 approved_by_1 null → is_active 2, not null → is_active 1
                    $q->where(function($q2) {
                            $q2->whereNull('approved_by_1')
                            ->where('is_active', 2);
                        })
                        ->orWhere(function($q3) use ($adminBuyerId, $buyerId) {
                            $q3->whereNotNull('approved_by_1')
                            ->where('is_active', 1)
                            ->whereNotIn('approved_by_1', [$adminBuyerId, $buyerId]);
                        });
                })
                ->get();




            if ($indents->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'There is no Unapproved Indent.'
                ]);
            }

            $data = [];
            $inventoryController = app(InventoryController::class);
            $permCtrl = app(InventoryPermissionCheckController::class);

            foreach ($indents as $indent) {

                $inventoryId = $indent->inventory_id;

                /** 🔹 RFQ data */
                $inventoryController->preloadRfqData([$inventoryId]);
                $rfqData = $inventoryController->getRfqData($inventoryId);
                $rfqQty  = $rfqData['rfq_qty'][$inventoryId] ?? 0;

                /** 🔹 Delete visibility + min qty */
                $restRfqQty     = $rfqQty;
                $min_indent_qty = 0;
                $showDelete     = true;

                $allIndents = Indent::where('inventory_id', $inventoryId)
                    ->where('is_deleted', 2)
                    ->where('inv_status', 1)
                    ->where('closed_indent', 2)
                    ->where('is_active', 2)
                    ->orderBy('id')
                    ->get(['id', 'indent_qty', 'is_active']);

                if ($indent->is_active != 2 && $rfqQty > 0) {

                    $showDelete     = false;
                    $min_indent_qty = $rfqQty;

                    foreach ($allIndents as $row) {

                        $used = IndentRfq::where('indent_id', $row->id)->exists();

                        if (!$used || $restRfqQty == 0) {
                            $showDelete = true;
                            $min_indent_qty = 0;
                            break;
                        }

                        if ($row->is_active == 1) {

                            if ($restRfqQty >= $row->indent_qty) {

                                if ($row->id == $indent->id) {
                                    $min_indent_qty = $restRfqQty;
                                    $showDelete = false;
                                    break;
                                }

                                $restRfqQty -= $row->indent_qty;

                            } elseif ($restRfqQty > 0) {

                                $min_indent_qty = $restRfqQty;
                                $restRfqQty = 0;

                                if ($row->id == $indent->id) {
                                    $showDelete = false;
                                    break;
                                }
                            }
                        }
                    }
                }

                /** 🔹 Approve button logic */
                $showApproveButton = '0';
                $buyerId = Auth::user()->parent_id ?: Auth::user()->id;

                if ($indent->is_active == 2) {

                    if (Auth::user()->parent_id == 0 ||
                        $permCtrl->checkPermissionForInventorySectionController('INDENT_APPROVE', 'add', '1')) {
                        $showApproveButton = '1';
                    }

                } else {

                    if (
                        !($indent->approved_by_1 && $indent->approved_by_2) &&
                        $indent->approved_by_1 != $buyerId
                    ) {
                        $count = $this->getNoOfIndentApprovedPermissionUser($buyerId);

                        if (
                            $count > 1 &&
                            $permCtrl->checkPermissionForInventorySectionController('INDENT_APPROVE', 'add', '1') &&
                            $indent->approved_by_1 != Auth::id()
                        ) {
                            $showApproveButton = '1';
                        }
                    }
                }

                /** 🔹 Push result */
                $data[] = [
                    'id'              => $indent->id,
                    'inventory_id'    => $indent->inventory_id,
                    'inventory_unique_id' => $indent->inventory_unique_id,
                    'indent_qty'      => round($indent->indent_qty, 3),
                    'remarks'         => cleanInvisibleCharacters($indent->remarks),
                    'is_active'       => $indent->is_active,
                    'created_by'      => $indent->created_by,
                    'updated_by'      => $indent->updated_by,
                    'created_at'      => $indent->created_at,
                    'updated_at'      => $indent->updated_at,
                    'showDelete'      => $showDelete,
                    'min_indent_qty'  => $min_indent_qty > 0 ? round($min_indent_qty, 3) : 0,

                    'product_name'  => optional($indent->inventory->product)->product_name,
                    'specification' => cleanInvisibleCharacters(optional($indent->inventory)->specification),
                    'size'          => cleanInvisibleCharacters(optional($indent->inventory)->size),
                    'uom_id'        => optional($indent->inventory)->uom_id,
                    'uom_name'      => optional($indent->inventory->uom)->uom_name,

                    'showApproveButton' => $showApproveButton,
                ];
            }

            return response()->json([
                'status' => 1,
                'data'   => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }



}
