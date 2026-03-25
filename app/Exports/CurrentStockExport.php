<?php

namespace App\Exports;

use App\Exports\Traits\ExportStylingTrait;
use App\Helpers\CurrentStockReportAmountHelper;
use App\Helpers\NumberFormatterHelper;
use App\Helpers\StockQuantityHelper;
use App\Http\Controllers\Buyer\InventoryController;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
};
use Carbon\Carbon;
class CurrentStockExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $currency;
    protected $controller;
    protected $filters;
    protected $quantityMaps;
    protected $amountMaps;
    protected $rowIndex = 0;
    use ExportStylingTrait;

    public function __construct(array $filters, $currency = '₹')
    {
        $this->filters = $filters;
        $this->currency = $currency;
        $this->controller = app()->make(InventoryController::class);
    }
    public function query()
    {
        $request = new \Illuminate\Http\Request($this->filters);
        $originalQuery = $this->controller->currentStockApplyFilters($request);

        $inventoryIds =  (clone $originalQuery)->pluck('id')->toArray();
        $from_date = null;
        $to_date   = null;

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to_date   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
        }
        $this->quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds,$from_date,$to_date);
        $this->amountMaps = CurrentStockReportAmountHelper::preloadValueMaps($inventoryIds,$from_date,$to_date);
        return $originalQuery ;
    }
    public function map($inv): array
    {
        $this->rowIndex++;
        $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$this->quantityMaps);
        $currentStockAmountValue = CurrentStockReportAmountHelper::calculateAmountValue($inv->id,$inv->opening_stock,$inv->stock_price,$this->amountMaps);
        $issueValue = $this->quantityMaps['issue'][$inv->id] ?? 0;
        $issueAmountValue =$this->amountMaps['issue'][$inv->id] ?? 0;
        $grnValue = $this->quantityMaps['grn'][$inv->id] ?? 0;
        $grnAmountValue = $this->amountMaps['grn'][$inv->id] ?? 0;

        $qtyMap = $this->quantityMaps;
        $amountValMap = $this->amountMaps;
        $issue_qty = $qtyMap['issue'][$inv->id] ?? 0;
        $issue_return_qty = $qtyMap['issue_return'][$inv->id] ?? 0;
        $total_issued_qty = $issue_qty - $issue_return_qty;

        $issue_amount = $amountValMap['issue'][$inv->id] ?? 0;
        $issue_return_amount = $amountValMap['issue_return'][$inv->id] ?? 0;
        $total_issued_amount = $issue_amount - $issue_return_amount;
        return [
            $this->rowIndex,
            $inv->branch->name ?? '',
            $inv->product->product_name ?? '',
            $inv->buyer_product_name,
            cleanInvisibleCharacters($inv->specification),
            cleanInvisibleCharacters($inv->size),
            $inv->inventory_grouping,
            $inv->uom->uom_name ?? '',
            " ".NumberFormatterHelper::formatQty($currentStockValue, $this->currency),
            " ".NumberFormatterHelper::formatCurrency($currentStockAmountValue, $this->currency),
            " ".NumberFormatterHelper::formatQty($total_issued_qty, $this->currency),
            " ".NumberFormatterHelper::formatCurrency($total_issued_amount, $this->currency),
            " ".NumberFormatterHelper::formatQty($grnValue, $this->currency),
            " ".NumberFormatterHelper::formatCurrency($grnAmountValue, $this->currency),
        ];
    }

    public function headings(): array
    {
        return [
            'Serial No','Branch', 'Product Name', 'Our Product Name', 'Specification',
            'Size','Inventory Grouping','UOM', 'Current Stock Quantity', 'Total Amount ('.$this->currency.')',
            'Issued Quantity', 'Issued Amount ('.$this->currency.')', 'GRN Quantity', 'GRN Amount ('.$this->currency.')',
        ];
    }
}
