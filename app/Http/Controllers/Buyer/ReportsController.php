<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Models\BranchDetail;
use App\Http\Controllers\Buyer\ManualPOController;
use App\Http\Controllers\Buyer\InventoryController;
use App\Models\Issueto;
use App\Models\Issued;
use App\Models\IssuedReturn;
use App\Models\IssuedType;
use App\Models\ReturnStock;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasModulePermission;

class ReportsController extends Controller
{
    use HasModulePermission;
    public function index()
    {
        $user_id = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
        $branches       =   BranchDetail::getDistinctActiveBranchesByUser($user_id);
        $categories     =   InventoryController::getSortedUniqueCategoryNames();
        $routeName = Route::currentRouteName(); // Get the route name
        ManualPOController::userCurrency();
        switch ($routeName) {
            case 'buyer.report.manualpo':
                session(['page_title' => 'Manual PO Report Management - Raprocure']);
                return view('buyer.report.manualpo', compact('branches', 'categories'));

            case 'buyer.report.workOrder':
                session(['page_title' => 'Work Order Report Management - Raprocure']);
                return view('buyer.report.workOrder', compact('branches', 'categories'));

            case 'buyer.report.minQty':
                session(['page_title' => 'Min Qty Report Management - Raprocure']);
                return view('buyer.report.minQty', compact('branches', 'categories'));

            case 'buyer.report.currentStock':
                session(['page_title' => 'Current Stock Report Management - Raprocure']);
                return view('buyer.report.currentStock', compact('branches', 'categories'));

            case 'buyer.report.consume':
                session(['page_title' => 'Consume Report Management - Raprocure']);
                return view('buyer.report.consume', compact('branches', 'categories'));

            case 'buyer.report.indent':
                try {
                    if (Auth::user()->parent_id != 0) {
                        $this->ensurePermission('INDENT', 'view', '1');
                    }
                    session(['page_title' => 'Indent Report Management - Raprocure']);
                    return view('buyer.report.indent', compact('branches', 'categories'));
                } catch (\Exception $e) {
                    return back()->with('error', $e->getMessage());
                }

            case 'buyer.report.closeindent':
                try {
                    if (Auth::user()->parent_id != 0) {
                        $this->ensurePermission('CLOSE_INDENT', 'view', '1');
                    }
                    session(['page_title' => 'Close Indent ReportManagement - Raprocure']);
                    return view('buyer.report.closeindent', compact('branches', 'categories'));
                } catch (\Exception $e) {
                    return back()->with('error', $e->getMessage());
                }

            case 'buyer.report.grn':
                try {
                    if (Auth::user()->parent_id != 0) {
                        $this->ensurePermission('GRN', 'view', '1');
                    }
                    session(['page_title' => 'GRN Report Management - Raprocure']);
                    return view('buyer.report.grn', compact('branches', 'categories'));
                } catch (\Exception $e) {
                    return back()->with('error', $e->getMessage());
                }

            case 'buyer.report.getPass':
                session(['page_title' => 'Gate Pass Entry Report Management - Raprocure']);
                return view('buyer.report.getPass', compact('branches', 'categories'));

            case 'buyer.report.pendingGrn':
                session(['page_title' => 'Pending GRN Report Management - Raprocure']);
                return view('buyer.report.pendingGrn', compact('branches', 'categories'));


            case 'buyer.report.pendingGrnStockReturn':
                session(['page_title' => 'Pending GRN Of Stock Return Report Management - Raprocure']);
                return view('buyer.report.pendingGrnStockReturn', compact('branches', 'categories'));


            case 'buyer.report.issued':
                try {
                    if (Auth::user()->parent_id != 0) {
                        $this->ensurePermission('ISSUED', 'view', '1');
                    }
                    session(['page_title' => 'Issued Report Management - Raprocure']);
                    $IssueTo     =   Issueto::orderBy('name', 'asc')->where('user_id', $user_id)->get();
                    $addUsers = Issued::whereNotNull('buyer_id')
                        ->where('buyer_id', $user_id)
                        ->with(['buyer:id,name', 'updater:id,name'])
                        ->get()
                        ->map(fn($item) =>
                            ($item->updated_by && $item->buyer_id != $item->updated_by)
                                ? (object)['id' => $item->updater->id ?? null, 'name' => $item->updater->name ?? null]
                                : (object)['id' => $item->buyer->id ?? null, 'name' => $item->buyer->name ?? null]
                        )
                        ->filter()
                        ->unique('id')
                        ->sortBy('name')
                        ->values();
                    return view('buyer.report.issued', compact('branches', 'categories','IssueTo','addUsers'));
                } catch (\Exception $e) {
                    return back()->with('error', $e->getMessage());
                }

            case 'buyer.report.issuereturn':
                session(['page_title' => 'Issued Return Report Management - Raprocure']);
                $addUsers = IssuedReturn::whereNotNull('buyer_id')
                    ->where('buyer_id', $user_id)
                    ->with(['buyer:id,name', 'updater:id,name'])
                    ->get()
                    ->map(fn($item) => ($item->updated_by && $item->buyer_id != $item->updated_by)
                        ? (object)['id' => $item->updater->id ?? null, 'name' => $item->updater->name ?? null]
                        : (object)['id' => $item->buyer->id ?? null, 'name' => $item->buyer->name ?? null]
                    )
                    ->filter()
                    ->unique('id')
                    ->sortBy('name')
                    ->values();
                return view('buyer.report.issuereturn', compact('branches', 'categories','addUsers'));
            case 'buyer.report.stockReturn':
                session(['page_title' => 'Stock Return Report Management - Raprocure']);
                $addUsers = ReturnStock::whereNotNull('buyer_id')
                    ->where('buyer_id', $user_id)
                    ->with(['buyer:id,name', 'updater:id,name'])
                    ->get()
                    ->map(fn($item) => ($item->updated_by && $item->buyer_id != $item->updated_by)
                        ? (object)['id' => $item->updater->id ?? null, 'name' => $item->updater->name ?? null]
                        : (object)['id' => $item->buyer->id ?? null, 'name' => $item->buyer->name ?? null]
                    )
                    ->filter()
                    ->unique('id')
                    ->sortBy('name')
                    ->values();
                $ReturnTypes     =   IssuedType::orderBy('name', 'asc')->get();
                return view('buyer.report.stockreturn', compact('branches','ReturnTypes', 'categories','addUsers'));

            case 'buyer.report.stockLedger':
                session(['page_title' => 'Stock Ledger Report Management - Raprocure']);
                return view('buyer.report.stockLedger', compact('branches', 'categories'));

            case 'buyer.report.productWiseStockLedger':
                session(['page_title' => 'Product Wise Stock Ledger Report Management - Raprocure']);
                return view('buyer.report.productWiseStockLedger', compact('branches', 'categories'));

            case 'buyer.report.deadStock':
                session(['page_title' => 'Dead Stock Report Management - Raprocure']);
                return view('buyer.report.deadStock', compact('branches', 'categories'));

            default:
                abort(404);
        }
    }
}
