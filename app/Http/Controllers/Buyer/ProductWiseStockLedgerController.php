<?php
namespace App\Http\Controllers\Buyer;

use App\Helpers\NumberFormatterHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventories;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Buyer\ManualPOController;
use App\Models\Grn;
use App\Models\Issued;
use App\Models\IssuedReturn;
use App\Models\ReturnStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class ProductWiseStockLedgerController extends Controller
{
    // 1 → Opening Stock
    // 2 → GRN
    // 3 → Stock Return
    // 4 → Issue
    // 5 → Issue Return

    public function index($id, Request $request)
    {
        // $inventory = Inventories::with('branch')->findOrFail($id);
        // $inventory = Inventories::findOrFail($id);
        $buyerId = Auth::user()->parent_id ?? Auth::id();
        $inventory = Inventories::where('id', $id)
        ->where('buyer_parent_id', $buyerId)
        ->with('branch')
        ->firstOrFail();
        ManualPOController::userCurrency();
        session(['branch_id' => $inventory->branch->branch_id ?? '']);
        session(['page_title' => 'Product Wise Stock Ledger Report']);
        return view('buyer.report.productWiseStockLedger', compact('inventory'));
    }
    public function fetchData(Request $request)
    {
        $inventoryId = $request->input('inventory_id');
        try {
            [$fromDate, $toDate] = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $mergedData = $this->getMergedStockData($inventoryId,$fromDate,$toDate);

        return DataTables::of($mergedData)
            ->addColumn('date', fn($row) => \Carbon\Carbon::parse($row->date)->format('d/m/Y'))
            ->addColumn('description', fn($row) => $this->getDescriptionLabel($row,false))
            ->addColumn('no', fn($row) => $row->no ?? '')
            ->addColumn('reference_number', fn($row) => $row->reference_number ?? '')
            ->addColumn('inward_quantity', fn($row) => $this->getInwardQuantity($row))
            ->addColumn('inward_total_amount', fn($row) => $this->getInwardTotalAmount($row))
            ->addColumn('outward_quantity', fn($row) => $this->getOutwardQuantity($row))
            ->addColumn('outward_total_amount', fn($row) => $this->getOutwardTotalAmount($row))
            ->addColumn('closing_quantity', fn($row) => '<span style="font-weight: bold;">' . $this->getClosingQuantity($row) . '</span>')
            ->addColumn('closing_total_amount', fn($row) => '<span style="font-weight: bold;">' . $this->getClosingTotalAmount($row) . '</span>')

            ->rawColumns(['description','closing_quantity','closing_total_amount'])
            ->make(true);
    }
    public function export(Request $request)
    {
        $inventoryId = $request->input('inventory_id');
        try {
            [$fromDate, $toDate] = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
        $mergedData = $this->getMergedStockData($inventoryId,$fromDate,$toDate);
        $userCurrency = session('user_currency')['symbol'] ?? '₹';
        $exportData = $this->prepareExportData($mergedData, $userCurrency);

        return response()->json([
            'count' => $exportData->count(),
            'data' => $exportData
        ]);
    }
    private function parseDateRange(Request $request): array
    {
        $fromDate = $toDate = null;

        if ($request->filled('from_date')) {
            try {
                $fromDate = Carbon::createFromFormat('d/m/Y', trim($request->input('from_date')))->startOfDay();
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid from_date format.');
            }
        }

        if ($request->filled('to_date')) {
            try {
                $toDate = Carbon::createFromFormat('d/m/Y', trim($request->input('to_date')))->endOfDay();
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid to_date format.');
            }
        }

        return [$fromDate, $toDate];
    }

    private function getMergedStockData($inventoryId,$fromDate,$toDate)
    {
        $openingBalance = collect();
        if ($fromDate) {
            $openingBalance = $this->getOpeningBalanceBefore($inventoryId, $fromDate);
        }
        $openingStock = $this->withOpeningStock($inventoryId,$fromDate,$toDate);
        $grnStock = $this->withGrnQty($inventoryId,$fromDate,$toDate);
        $issueStock = $this->withIssueQty($inventoryId,$fromDate,$toDate);
        $issueReturnStock = $this->withIssueReturnQty($inventoryId,$fromDate,$toDate);
        $stockReturnStock = $this->withStockReturnQty($inventoryId,$fromDate,$toDate);

        return $openingBalance->merge($openingStock)->merge($grnStock)->merge($issueStock)->merge($issueReturnStock)->merge($stockReturnStock)->sortBy('date')->values();
    }

    private function prepareExportData($mergedData, $userCurrency)
    {
        $closingQty = 0;
        $closingAmount = 0;

        return $mergedData->map(function ($row) use (&$closingQty, &$closingAmount, $userCurrency) {
            $inwardQty = in_array($row->status, [1, 2, 5]) ? $row->qty : null;
            $inwardAmount = $inwardQty ? round($inwardQty * $row->rate,2) : null;

            $outwardQty = in_array($row->status, [3, 4]) ? $row->qty : null;
            $outwardAmount = $outwardQty ? round($outwardQty * $row->rate,2) : null;
            if ($inwardQty !== null) {
                $closingQty += $inwardQty;
                $closingAmount += $inwardAmount;
            }

            if ($outwardQty !== null) {
                $closingQty -= $outwardQty;
                $closingAmount -= $outwardAmount;
            }
            $closingQty = in_array($row->status, [6]) ? $row->qty:$closingQty;
            $closingAmount = in_array($row->status, [6]) ? $row->rate:$closingAmount;

            return [
                'Date' => \Carbon\Carbon::parse($row->date)->format('d/m/Y'),
                'Particulars / Description' => $this->getDescriptionLabel($row,true),
                'No.' => $row->no ?? '',
                'Reference Number' => $row->reference_number ?? '',
                'Inward Quantity (' . $row->uom_name . ')' => $inwardQty !== null ?  NumberFormatterHelper::formatQty($inwardQty,session('user_currency')['symbol'] ?? '₹') : '-',
                'Inward Total Amount (' . $userCurrency . ')' => $inwardAmount !== null ? NumberFormatterHelper::formatCurrency($inwardAmount, session('user_currency')['symbol'] ?? '₹') : '-',
                'Outward Quantity (' . $row->uom_name . ')' => $outwardQty !== null ?  NumberFormatterHelper::formatQty($outwardQty,session('user_currency')['symbol'] ?? '₹') : '-',
                'Outward Total Amount (' . $userCurrency . ')' => $outwardAmount !== null ? NumberFormatterHelper::formatCurrency($outwardAmount, session('user_currency')['symbol'] ?? '₹') : '-',
                'Closing Quantity (' . $row->uom_name . ')' => $closingQty <= 0 ? '0' :  NumberFormatterHelper::formatQty($closingQty,session('user_currency')['symbol'] ?? '₹'),
                'Closing Total Amount (' . $userCurrency . ')' => NumberFormatterHelper::formatCurrency($closingAmount, session('user_currency')['symbol'] ?? '₹'),
            ];
        });
    }

    private function getDescriptionLabel($row,$excle)
    {
        $descriptions = [
            1 => 'Opening Stock',
            2 => 'GRN',
            3 => 'Stock Return',
            4 => 'Issue',
            5 => 'Issue Return',
            6 => 'Closing Value',
        ];
        $labelClasses = [
            1 => 'success',
            2 => 'success',
            3 => 'danger',
            4 => 'danger',
            5 => 'success',
            6 => 'success',
        ];
        $label = $descriptions[$row->status] ?? 'Unknown';
        $class = $labelClasses[$row->status] ?? 'secondary';
        if($excle){
            return $label;
        }

        return '<span class="text-' . $class . '" style="font-weight: bold;">' . $label . '</span>';
        }
    private function getInwardQuantity($row)
    {
        return in_array($row->status, [1, 2, 5]) ?  NumberFormatterHelper::formatQty($row->qty,session('user_currency')['symbol'] ?? '₹') : '-';
    }
    private function getInwardTotalAmount($row)
    {
        $userCurrency = session('user_currency')['symbol'] ?? '₹';
        return in_array($row->status, [1, 2, 5]) ? NumberFormatterHelper::formatCurrency(round($row->qty * $row->rate,2), $userCurrency) : '-';
    }
    private function getOutwardQuantity($row)
    {
        return in_array($row->status, [3, 4]) ?  NumberFormatterHelper::formatQty($row->qty,session('user_currency')['symbol'] ?? '₹') : '-';
    }
    private function getOutwardTotalAmount($row)
    {
        $userCurrency = session('user_currency')['symbol'] ?? '₹';
        return in_array($row->status, [3, 4]) ? NumberFormatterHelper::formatCurrency(round($row->qty * $row->rate,2), $userCurrency) : '-';
    }
    private function getClosingQuantity($row)
    {
        static $closingQty = 0;
        if (in_array($row->status, [1, 2, 5])) {
            $closingQty += $row->qty;
        } elseif (in_array($row->status, [3, 4])) {
            $closingQty -= $row->qty;
        } elseif (in_array($row->status, [6])) {
            $closingQty = $row->qty;
        }
        return $closingQty <= 0 ? '0' :  NumberFormatterHelper::formatQty($closingQty,session('user_currency')['symbol'] ?? '₹');
    }
    private function getClosingTotalAmount($row)
    {
        static $closingAmount = 0;
        $amount = round($row->qty * $row->rate,2);

        if (in_array($row->status, [1, 2, 5])) {
            $closingAmount += $amount;
        } elseif (in_array($row->status, [3, 4])) {
            $closingAmount -= $amount;
        }elseif (in_array($row->status, [6])) {
            $closingAmount = $row->rate;
        }

        return NumberFormatterHelper::formatCurrency($closingAmount, session('user_currency')['symbol'] ?? '₹');
    }
    private function withOpeningStock($inventoryId, $fromDate = null, $toDate = null)
    {
        $query = Inventories::query()->where('id', $inventoryId);

        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }
        return $query->get()->map(function ($item) {
            $item->status = 1;
            $item->qty = $item->opening_stock;
            $item->rate = $item->stock_price;
            $item->date = $item->created_at;
            return $item;
        });
    }

    private function withGrnQty($inventoryId, $fromDate = null, $toDate = null)//pingki
    {
        $query = Grn::query()->with('stock','inventory')->where('inventory_id', $inventoryId);

        if ($fromDate && $toDate) {
            $query->whereBetween('updated_at', [$fromDate, $toDate]);
        }
        return $query->get()->map(function ($item) {
            $item->status = 2;
            $item->qty = $item->grn_qty;
            $item->no = $item->grn_no;
            $item->date = $item->updated_at;
            if($item->order_id=='0' && $item->stock_return_for=='0'){
                $item->reference_number = 'Stock Return No ' . optional($item->stock)->stock_no;
                $item->rate = $item->order_rate;
            }
            elseif ($item->order_id == '0' && $item->stock_return_for != '0') {
                $item->reference_number = 'Stock Return No ' . optional($item->stock)->stock_no;

                $originalGrnId = optional($item->stock)->stock_return_for;
                $originalGrn = Grn::with(['manualOrderProduct', 'order.order_variants', 'inventory'])->find($originalGrnId);

                $item->rate = $originalGrn ? $originalGrn->getOrderRateAttribute() : null;

            } else {
                $item->reference_number = $item->po_number;
                $item->rate = $item->order_rate;
            }


            return $item;
        });
    }
    private function withIssueQty($inventoryId, $fromDate = null, $toDate = null)
    {
        $query = Issued::with('grn.manualOrderProduct', 'grn.manualOrder', 'inventory')
            ->where('inventory_id', $inventoryId);

        if ($fromDate && $toDate) {
            $query->whereBetween('updated_at', [$fromDate, $toDate]);
        }

        return $query->get()->map(function ($item) {
            $item->status = 4;
            $item->no = $item->issued_no;
            $item->reference_number = $item->reference_number;
            $item->qty = $item->qty;
            $item->rate = $item->rate;
            $item->date = $item->updated_at;
            return $item;
        });
    }
    private function withIssueReturnQty($inventoryId, $fromDate = null, $toDate = null)
    {
        $query = IssuedReturn::with('grn.manualOrderProduct', 'grn.manualOrder', 'inventory')
            ->where('inventory_id', $inventoryId);

        if ($fromDate && $toDate) {
            $query->whereBetween('updated_at', [$fromDate, $toDate]);
        }

        return $query->get()->map(function ($item) {
            $item->status = 5;
            $item->no = $item->issue_unique_no;
            $item->reference_number = $item->reference_number;
            $item->qty = $item->qty;
            $item->rate = $item->rate;
            $item->date = $item->updated_at;
            return $item;
        }) ;
    }
    private function withStockReturnQty($inventoryId, $fromDate = null, $toDate = null)
    {
        $query = ReturnStock::with('grn.manualOrderProduct', 'grn.manualOrder', 'inventory')
            ->where('inventory_id', $inventoryId);

        if ($fromDate && $toDate) {
            $query->whereBetween('updated_at', [$fromDate, $toDate]);
        }

        return $query->get()->map(function ($item) {
            $item->status = 3;
            $item->no = $item->stock_no;
            $item->reference_number = $item->reference_number;
            $item->qty = $item->qty;
            $item->rate = $item->rate;
            $item->date = $item->updated_at;
            return $item;
        }) ;
    }



    private function getOpeningBalanceBefore($inventoryId, $fromDate)
    {
        $transactions = collect()
            ->merge($this->withOpeningStock($inventoryId))
            ->merge($this->withGrnQty($inventoryId))
            ->merge($this->withIssueQty($inventoryId))
            ->merge($this->withIssueReturnQty($inventoryId))
            ->merge($this->withStockReturnQty($inventoryId));

        $filtered = $transactions->filter(fn($item) => $item->date < $fromDate);

        $closingQty = 0;
        $closingAmount = 0;

        foreach ($filtered as $item) {
            $amount =round($item->qty * $item->rate, 2);
            if (in_array($item->status, [1, 2, 5])) {
                $closingQty += $item->qty;
                $closingAmount += $amount;
            } elseif (in_array($item->status, [3, 4])) {
                $closingQty -= $item->qty;
                $closingAmount -= $amount;
            }
        }

        if ($filtered->isEmpty()) {
            return collect();
        }

        return collect([(object)[
            'status' => 6,
            'qty' => $closingQty,
            'rate' => $closingQty != 0 ? $closingAmount : 0,
            'date' => \Carbon\Carbon::parse($fromDate)->subDay()->startOfDay(),
            'description' => 'Opening Balance',
            'no' => null,
            'reference_number' => null,
            'uom_name' => $filtered->first()->uom_name ?? '',
        ]]);
    }
}
